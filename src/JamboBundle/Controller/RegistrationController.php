<?php

namespace JamboBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use JamboBundle\Entity\Participant;
use JamboBundle\Entity\Patrol;
use JamboBundle\Entity\Repository\BaseRepositoryInterface;
use JamboBundle\Entity\Repository\ParticipantRepository;
use JamboBundle\Entity\Repository\TroopRepository;
use JamboBundle\Entity\Troop;
use JamboBundle\Exception\RegistrationException;
use JamboBundle\Form\Type\PatrolType;
use JamboBundle\Form\Type\TroopType;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zend\EventManager\Exception\ExceptionInterface;

/**
 * Controller
 */
class RegistrationController extends Controller
{
    /**
     * Index action
     *
     * @return Response
     */
    public function indexAction()
    {
        if ($this->participantsLimitsExceeded()) {
            return $this->render('JamboBundle::registration/index.html.twig', [
                'participantsLimitsExceeded' => true,
            ]);
        }

        return $this->redirect($this->generateUrl('registration_troop_form'));
    }

    /**
     * Troop form action
     *
     * @param Request $request request
     *
     * @return Response
     */
    public function troopFormAction(Request $request)
    {
        if ($this->participantsLimitsExceeded()) {
            return $this->render('JamboBundle::registration/troop/closed.html.twig', [
                'participantsLimitsExceeded' => true,
            ]);
        }

        $troop = new Troop();
        $form = $this->createForm(TroopType::class, $troop, [
            'action' => $this->generateUrl('registration_troop_form'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $createdAt = new DateTime();
            $troop->setStatus(Troop::STATUS_NOT_COMPLETED)
                ->setActivationHash($this->generateActivationHash())
                ->setCreatedAt($createdAt)
                ->setUpdatedAt($createdAt);

            try {
                $this->get('jambo_bundle.repository.troop')
                    ->insert($troop, true);
                $this->addMessage('troop.message.success', 'success');
                $response = $this->redirect($this->generateUrl('registration_patrol_form', [
                    'troopHash' => $troop->getActivationHash(),
                ]));
            } catch (Exception $e) {
                $this->addMessage($e->getMessage(), 'error');
            }
        }
        if (!isset($response)) {
            $this->addErrorMessage($form);
            $response = $this->render('JamboBundle::registration/troop/form.html.twig', [
                'form' => $form->createView(),
                'patrol_limit' => $this->getParameter('jambo.patrol_limit'),
            ]);
        }

        return $response;
    }

    /**
     * Patrol form action
     *
     * @param int     $troopHash troop hash
     * @param Request $request   request
     *
     * @return Response
     */
    public function patrolFormAction($troopHash, Request $request)
    {
        if ($this->participantsLimitsExceeded(false)) {
            return $this->render('JamboBundle::registration/troop/closed.html.twig', [
                'participantsLimitsExceeded' => true,
            ]);
        }

        /** @var Troop $troop */
        $troop = $this
            ->get('jambo_bundle.repository.troop')
            ->findOneByOrException([
                'activationHash' => $troopHash,
                'status' => Troop::STATUS_NOT_COMPLETED,
            ]);

        $patrolLimit = $this->getParameter('jambo.patrol_limit');
        $patrolsNumber = $troop->countPatrols();
        if ($patrolsNumber >= $patrolLimit) {
            return $this->render('JamboBundle::registration/patrol/enough.html.twig');
        }
        $patrolNo = $patrolsNumber + 1;
        $isFirstPatrol = $patrolsNumber < 1;
        $isLastPatrol = $patrolNo == $patrolLimit;

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $patrol = new Patrol();
        $patrol->setTroop($troop);
        $leader = new Participant();
        $leader->setPatrol($patrol);
        $troop->addPatrol($patrol);
        $patrol
            ->setLeader($leader)
            ->addMember($leader)
        ;
        $participantMinSize = $this->getParameter('jambo.participant_limit.min');
        $participantMaxSize = $this->getParameter('jambo.participant_limit.max');
        for ($i = 1; $i < $participantMinSize; $i++) {
            $member = new Participant();
            $member->setPatrol($patrol);
            $patrol->addMember($member);
        }
        $form = $this->createForm(PatrolType::class, $patrol, [
            'action' => $this->generateUrl('registration_patrol_form', [
                'troopHash' => $troop->getActivationHash(),
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $createdAt = new DateTime();
            $patrol
                ->setCreatedAt($createdAt)
                ->setUpdatedAt($createdAt)
            ;
            if ($isFirstPatrol) {
                $troop
                    ->setActivationHash($this->generateActivationHash($leader->getEmail()))
                    ->setLeader($leader)
                ;
                $troopLeader = $leader;
            } else {
                if ($isLastPatrol) {
                    $troop->setStatus(Troop::STATUS_COMPLETED);
                }
                $troopLeader = $troop->getLeader();
            }

            $members = $patrol->getMembers();
            if ($members->count() > $participantMaxSize) {
                $patrol->setMembers(new ArrayCollection($members->slice(0, $participantMaxSize)));
            }
            unset($members);
            foreach ($patrol->getMembers() as $i => $member) {
                /** @var Participant $member */
                $isLeader = $member === $patrol->getLeader();
                // Copies data from form to each participant
                $member
                    ->setStatus(Participant::STATUS_COMPLETED)
                    ->setActivationHash($this->generateActivationHash($member->getEmail()))
                    ->setPatrol($patrol)
                    ->setCreatedAt($createdAt)
                    ->setUpdatedAt($createdAt)
                    ->setSex($member->getSexFromPesel())
                ;
                if ($member->getDistrictId() == null) {
                    $member->setDistrictId($patrol->getDistrictId());
                }

                /** @var FormInterface $memberView */
                $memberView = $form
                    ->get('members')
                    ->get($i)
                ;
                // Validates age
                $this->validateAge($member, $memberView->get('pesel'),
                $isLeader ? 'jambo.age_limit.adult' : 'jambo.age_limit.min');
                // Validates PESEL existance
                if ($member->getPesel() == null) {
                    $memberView->get('pesel')
                        ->addError(new FormError($translator->trans('form.error.pesel_empty')));
                }
/*                if ($isLeader) {
                    // Validates leader grade - disabled
                        if ($member->getGradeId() == RegistrationLists::GRADE_NO) {
                            $memberView->get('gradeId')
                                ->addError(new FormError($translator->trans('form.error.grade_inproper')));
                        }
                }*/
            }

            if ($form->isValid()) {
                try {
                    if ($isLastPatrol) {
                        $this->mailSendingProcedure($troopLeader->getEmail(),
                            $translator->trans('email.troop.registration_title'),
                            'JamboBundle::registration/troop/email_confirm.html.twig', [
                                'confirmationUrl' => $this->generateUrl('registration_troop_confirm', [
                                    'hash' => $troop->getActivationHash(),
                                ], UrlGeneratorInterface::ABSOLUTE_URL),
                                'leader' => $troopLeader,
                            ]);
                    } elseif ($isFirstPatrol) {
                        $this->mailSendingProcedure($troopLeader->getEmail(),
                            $translator->trans('email.troop.completation_title'),
                            'JamboBundle::registration/troop/email_complete.html.twig', [
                                'completationUrl' => $this->generateUrl('registration_patrol_form', [
                                    'troopHash' => $troop->getActivationHash(),
                                ], UrlGeneratorInterface::ABSOLUTE_URL),
                                'leader' => $troopLeader,
                            ]);
                    }

                    try {
                        $this->get('jambo_bundle.repository.troop')
                            ->update($troop, true);
                        $this->get('jambo_bundle.repository.patrol')
                            ->insert($patrol, true);
                        $this->get('jambo_bundle.repository.participant')
                            ->insert($leader, true);
                    } catch (Exception $e) {
                        throw new RegistrationException('form.exception.database', 0, $e);
                    }

                    if ($isLastPatrol) {
                        $successMessage = $translator->trans('success.message', [
                            '%email%' => $troopLeader->getEmail(),
                        ]);
                        $redirectLink = $this->generateUrl('registration_success');
                    } else {
                        $successMessage = $translator->trans('patrol.message.success', [
                            '%troopName%' => $troop->getName(),
                        ]);
                        $redirectLink = $this->generateUrl('registration_patrol_form', [
                            'troopHash' => $troop->getActivationHash(),
                        ]);
                    }
                    $this->addMessage($successMessage, 'success');
                    $response = $this->redirect($redirectLink);
                } catch (ExceptionInterface $e) {
                    $this->addMessage($e->getMessage(), 'error');
                } catch (Exception $e) {
                    $this->addMessage('form.exception.database', 'error');
                }
            }
        }
        if (!isset($response)) {
            $this->addErrorMessage($form);
            $response = $this->render('JamboBundle::registration/patrol/form.html.twig', [
                'form' => $form->createView(),
                'is_first_patrol' => $isFirstPatrol,
                'max_size' => $participantMaxSize,
                'min_age_member' => $this->getParameter('jambo.age_limit.min'),
                'min_size' => $participantMinSize,
                'patrol_no' => $patrolNo,
                'troop' => $troop,
            ]);
        }

        return $response;
    }

    /**
     * Success action
     *
     * @return Response
     */
    public function successAction()
    {
        return $this->render('JamboBundle::registration/success.html.twig', [
            'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
        ]);
    }

    /**
     * Participant confirm action
     *
     * @param string $hash hash
     *
     * @return Response
     */
    public function participantConfirmAction($hash)
    {
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $this->get('jambo_bundle.repository.participant');
        $response = $this->confirmationProcedureParticipant($participantRepository, $hash, Participant::STATUS_CONFIRMED);

        return $response;
    }

    /**
     * Troop confirm action
     *
     * @param string $hash hash
     *
     * @return Response
     */
    public function troopConfirmAction($hash)
    {
        /** @var TroopRepository $troopRepository */
        $troopRepository = $this->get('jambo_bundle.repository.troop');
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $this->get('jambo_bundle.repository.participant');
        $response = $this->confirmationProcedureTroop($troopRepository, $participantRepository, $hash,
            Troop::STATUS_CONFIRMED, Participant::STATUS_CONFIRMED);

        return $response;
    }

    /**
     * Mail sending procedure
     *
     * @param string $emailAddress e-mail address
     * @param string $emailTitle   e-mail title
     * @param string $emailView    e-mail view
     * @param array  $emailParams  e-mail params
     *
     * @throws RegistrationException
     */
    private function mailSendingProcedure($emailAddress, $emailTitle, $emailView, array $emailParams = [])
    {
        $message = Swift_Message::newInstance()
            ->setSubject($emailTitle)
            ->setFrom($this->getParameter('mailer_user'))
            ->setTo($emailAddress)
            ->setReplyTo($this->getParameter('jambo.email.reply_to'))
            ->setBody($this->renderView($emailView, $emailParams), 'text/html');

        $mailer = $this->get('mailer');
        if (!$mailer->send($message)) {
            throw new RegistrationException('form.exception.email');
        }
    }

    /**
     * Confirmation procedure participant
     *
     * @param BaseRepositoryInterface $repository repository
     * @param string                  $hash       hash
     * @param integer                 $status     status
     *
     * @return Response
     */
    private function confirmationProcedureParticipant(BaseRepositoryInterface $repository, $hash, $status)
    {
        /** @var Participant|null $participant */
        $participant = $repository->findOneBy(array(
            'activationHash' => $hash,
        ));

        if (!isset($participant) || !$participant->isCompleted() || $participant->isConfirmed()) {
            $this->addMessage('confirmation.error', 'error');
        } else {
            $participant->setStatus($status);
            $repository->update($participant, true);
            $this->addMessage('confirmation.success', 'success');
        }

        return $this->render('JamboBundle::registration/confirmation.html.twig', [
            'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
        ]);
    }

    /**
     * Confirmation procedure troop
     *
     * @param BaseRepositoryInterface $troopRepository       troop repository
     * @param BaseRepositoryInterface $participantRepository participant repository
     * @param string                  $hash                  hash
     * @param int                     $troopStatus           troop status
     * @param int                     $participantStatus     participant status
     *
     * @return Response
     */
    private function confirmationProcedureTroop(BaseRepositoryInterface $troopRepository,
        BaseRepositoryInterface $participantRepository, $hash, $troopStatus, $participantStatus)
    {
        /** @var Troop|null $troop */
        $troop = $troopRepository->findOneBy(array(
            'activationHash' => $hash,
        ));

        if (!isset($troop) || !$troop->isCompleted() || $troop->isConfirmed()) {
            $this->addMessage('confirmation.error', 'error');
        } else {
            $troop->setStatus($troopStatus);
            $troopRepository->update($troop, true);
            /** @var Participant $participant */
            $participant = $troop->getLeader();
            if (!$participant->isConfirmed()) {
                $participant->setStatus($participantStatus);
                $participantRepository->update($participant, true);
            }
            $this->addMessage('confirmation.success', 'success');
        }

        return $this->render('JamboBundle::registration/confirmation.html.twig', [
            'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
        ]);
    }

    /**
     * Validate age
     *
     * @param Participant   $participant     participant
     * @param FormInterface $ageField        age field
     * @param string        $minAgeParamName min age param name
     */
    private function validateAge(Participant $participant, FormInterface $ageField, $minAgeParamName)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $ageMin = $this->getParameter($minAgeParamName);
        $ageMax = $this->getParameter('jambo.age_limit.max');
        $ageLimit = new DateTime($this->getParameter('jambo.age_limit.date'));

        $birthDate = $participant->getBirthDate();
        if (!isset($birthDate)) {
            $ageField->addError(new FormError($translator->trans('form.error.birth_date_not_specified')));
        } else {
            $age = (int) $birthDate->diff($ageLimit->modify('-1 day'))
                ->format('%y');
            if ($age < $ageMin) {
                $ageField->addError(new FormError($translator->trans('form.error.age_too_low', [
                    '%age%' => $ageMin,
                ])));
            } elseif ($age > $ageMax) {
                $ageField->addError(new FormError($translator->trans('form.error.age_too_high', [
                    '%age%' => $ageMax,
                ])));
            }
        }
    }

    /**
     * Generate activation hash
     *
     * @param string $email e-mail
     *
     * @return string
     */
    private function generateActivationHash($email = '')
    {
        $activationHash = md5(implode('-', [
            $email,
            time(),
            rand(10000, 99999),
        ]));

        return $activationHash;
    }

    /**
     * Add error message
     *
     * @param FormInterface $form
     */
    private function addErrorMessage(FormInterface $form)
    {
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addMessage('form.errors', 'error');
        }
    }

    /**
     * Add message
     *
     * @param string $message message
     * @param string $type    type
     *
     * @return self
     */
    private function addMessage($message, $type = 'message')
    {
        $this->get('session')
            ->getFlashBag()
            ->add($type, $message);

        return $this;
    }

    /**
     * Participants limits exceeded
     *
     * @param bool $checkTroopLimit check troop limit
     *
     * @return bool
     */
    private function participantsLimitsExceeded($checkTroopLimit = true)
    {
        $timeLimit = new DateTime($this->getParameter('jambo.time_limit.participants'));
        if (new DateTime('now') > $timeLimit) {
            return true;
        }

        if ($checkTroopLimit) {
            $troopLimit = $this->getParameter('jambo.troop_limit');
            $troopRepository = $this->get('jambo_bundle.repository.troop');
            if ($troopRepository->getTotalNumber() >= $troopLimit) {
                return true;
            }
        }

        return false;
    }
}
