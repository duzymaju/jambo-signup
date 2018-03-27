<?php

namespace JamboBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use JamboBundle\Entity\Participant;
use JamboBundle\Entity\Patrol;
use JamboBundle\Entity\Troop;
use JamboBundle\Exception\ExceptionInterface;
use JamboBundle\Exception\RegistrationException;
use JamboBundle\Form\Type\PatrolMembersType;
use JamboBundle\Form\Type\PatrolType;
use JamboBundle\Form\Type\TroopType;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
        return $this->render('JamboBundle::registration/index.html.twig', [
            'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
        ]);
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
        $this->addMembersToPatrol($patrol);
        $form = $this->createForm(PatrolType::class, $patrol, [
            'action' => $this->generateUrl('registration_patrol_form', [
                'troopHash' => $troop->getActivationHash(),
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->preparePatrolAndMembers($form, $patrol);
            if ($isFirstPatrol) {
                $troop->setLeader($leader);
                $troopLeader = $leader;
            } else {
                if ($isLastPatrol) {
                    $troop->setStatus(Troop::STATUS_COMPLETED);
                }
                $troopLeader = $troop->getLeader();
            }

            if ($form->isValid()) {
                try {
                    if ($isLastPatrol) {
                        $troop->setActivationHash($this->generateActivationHash($troopLeader->getEmail()));
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
                        $successMessage = $translator->trans('success.message.notification', [
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
                'max_size' => $this->getParameter('jambo.participant_limit.max'),
                'min_age_member' => $this->getParameter('jambo.age_limit.min'),
                'min_size' => $this->getParameter('jambo.participant_limit.min'),
                'patrol_no' => $patrolNo,
                'troop' => $troop,
            ]);
        }

        return $response;
    }

    /**
     * Patrol single form action
     *
     * @param Request $request request
     *
     * @return Response
     */
    public function patrolSingleFormAction(Request $request)
    {
        if ($this->participantsLimitsExceeded(false)) {
            return $this->render('JamboBundle::registration/troop/closed.html.twig', [
                'participantsLimitsExceeded' => true,
            ]);
        }

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $patrol = new Patrol();
        $leader = new Participant();
        $leader->setPatrol($patrol);
        $patrol
            ->setLeader($leader)
            ->addMember($leader)
        ;
        $this->addMembersToPatrol($patrol);
        $form = $this->createForm(PatrolType::class, $patrol, [
            'action' => $this->generateUrl('registration_patrol_single_form'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->preparePatrolAndMembers($form, $patrol);

            if ($form->isValid()) {
                try {
                    $this->mailSendingProcedure($leader->getEmail(),
                        $translator->trans('email.patrol.registration_title'),
                        'JamboBundle::registration/patrol/email_confirm.html.twig', [
                            'confirmationUrl' => $this->generateUrl('registration_patrol_confirm', [
                                'hash' => $patrol->getActivationHash(),
                            ], UrlGeneratorInterface::ABSOLUTE_URL),
                            'leader' => $leader,
                        ]);

                    try {
                        $this
                            ->get('jambo_bundle.repository.patrol')
                            ->insert($patrol, true)
                        ;
                        $this
                            ->get('jambo_bundle.repository.participant')
                            ->insert($leader, true)
                        ;
                    } catch (Exception $e) {
                        throw new RegistrationException('form.exception.database', 0, $e);
                    }

                    $this->addMessage($translator->trans('success.message.notification', [
                        '%email%' => $leader->getEmail(),
                    ]), 'success');
                    $response = $this->redirectToRoute('registration_success');
                } catch (ExceptionInterface $e) {
                    $this->addMessage($e->getMessage(), 'error');
                } catch (Exception $e) {
                    $this->addMessage('form.exception.database', 'error');
                }
            }
        }
        if (!isset($response)) {
            $this->addErrorMessage($form);
            $response = $this->render('JamboBundle::registration/patrol/single_form.html.twig', [
                'form' => $form->createView(),
                'max_size' => $this->getParameter('jambo.participant_limit.max'),
                'min_age_member' => $this->getParameter('jambo.age_limit.min'),
                'min_size' => $this->getParameter('jambo.participant_limit.min'),
            ]);
        }

        return $response;
    }

    /**
     * Patrol participants form action
     *
     * @param int     $hash    hash
     * @param Request $request request
     *
     * @return Response
     */
    public function patrolParticipantsFormAction($hash, Request $request)
    {
        if ($this->patrolParticipantsLimitsExceeded()) {
            return $this->render('JamboBundle::registration/troop/closed.html.twig', [
                'participantsLimitsExceeded' => true,
            ]);
        }

        /** @var Patrol $patrol */
        $patrol = $this
            ->get('jambo_bundle.repository.patrol')
            ->findOneByOrException([
                'activationHash' => $hash,
                'status' => Patrol::STATUS_CONFIRMED,
            ]);

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $membersToLeave = $patrol
            ->getMembers()
            ->count()
        ;
        $maxParticipantsLimit = $this->getParameter('jambo.participant_limit.max');
        if ($membersToLeave >= $maxParticipantsLimit) {
            throw new NotFoundHttpException('There is no possibility to add new participants to this patrol.');
        }

        // Just a workaround to prevent from editing existing participants' data in a simpliest possible way
        $surrogatePatrol = new Patrol();
        $surrogatePatrol->setName('surrogate');
        $this->addMembersToPatrol($surrogatePatrol, $membersToLeave);
        $form = $this->createForm(PatrolMembersType::class, $surrogatePatrol, [
            'action' => $this->generateUrl('registration_patrol_participants_form', [
                'hash' => $patrol->getActivationHash(),
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->preparePatrolAndMembers($form, $surrogatePatrol);

            if ($form->isValid()) {
                foreach ($surrogatePatrol->getMembers() as $member) {
                    $member->setPatrol($patrol);
                    $patrol->addMember($member);
                }
                try {
                    try {
                        $this
                            ->get('jambo_bundle.repository.patrol')
                            ->update($patrol, true)
                        ;
                    } catch (Exception $e) {
                        throw new RegistrationException('form.exception.database', 0, $e);
                    }

                    $this->addMessage($translator->trans('success.message.participants'), 'success');
                    $response = $this->redirectToRoute('registration_success');
                } catch (ExceptionInterface $e) {
                    $this->addMessage($e->getMessage(), 'error');
                } catch (Exception $e) {
                    $this->addMessage('form.exception.database', 'error');
                }
            }
        }
        if (!isset($response)) {
            $troop = $patrol->getTroop();
            $this->addErrorMessage($form);
            $response = $this->render('JamboBundle::registration/patrol/participants_form.html.twig', [
                'added_items' => $membersToLeave,
                'form' => $form->createView(),
                'max_size' => $this->getParameter('jambo.participant_limit.max'),
                'min_age_member' => $this->getParameter('jambo.age_limit.min'),
                'min_size' => $this->getParameter('jambo.participant_limit.min'),
                'patrol' => $patrol,
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
        /** @var Participant|null $participant */
        $participant = $this
            ->get('jambo_bundle.repository.participant')
            ->findOneBy(array(
                'activationHash' => $hash,
            ))
        ;
        try {
            $this->confirmParticipant($participant);
            $this->addMessage('confirmation.success', 'success');
        } catch (Exception $e) {
            $this->addMessage('confirmation.error', 'error');
        }

        return $this->render('JamboBundle::registration/confirmation.html.twig', [
            'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
        ]);
    }

    /**
     * Patrol confirm action
     *
     * @param string $hash hash
     *
     * @return Response
     */
    public function patrolConfirmAction($hash)
    {
        /** @var Patrol|null $patrol */
        $patrol = $this
            ->get('jambo_bundle.repository.patrol')
            ->findOneBy(array(
                'activationHash' => $hash,
            ))
        ;
        try {
            $this->confirmPatrol($patrol);
            $this->addMessage('confirmation.success', 'success');
        } catch (Exception $e) {
            $this->addMessage('confirmation.error', 'error');
        }

        return $this->render('JamboBundle::registration/confirmation.html.twig', [
            'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
        ]);
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
        /** @var Troop|null $troop */
        $troop = $this
            ->get('jambo_bundle.repository.troop')
            ->findOneBy(array(
                'activationHash' => $hash,
            ))
        ;
        try {
            $this->confirmTroop($troop);
            $this->addMessage('confirmation.success', 'success');
        } catch (Exception $e) {
            $this->addMessage('confirmation.error', 'error');
        }

        return $this->render('JamboBundle::registration/confirmation.html.twig', [
            'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
        ]);
    }

    /**
     * Add members to patrol
     *
     * @param Patrol $patrol         patrol
     * @param int    $membersToLeave number of members not to prepare
     *
     * @return self
     */
    private function addMembersToPatrol(Patrol $patrol, $membersToLeave = 0)
    {
        $membersToAdd = $this->getParameter('jambo.participant_limit.min') - $membersToLeave;
        for ($i = 0; $i < $membersToAdd; $i++) {
            $member = new Participant();
            $member->setPatrol($patrol);
            $patrol->addMember($member);
        }

        return $this;
    }

    /**
     * Prepare patrol and members
     *
     * @param Form   $form   form
     * @param Patrol $patrol patrol
     *
     * @return self
     */
    private function preparePatrolAndMembers(Form $form, Patrol $patrol)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        $participantMaxSize = $this->getParameter('jambo.participant_limit.max');

        $createdAt = new DateTime();
        $patrol
            ->setStatus(Patrol::STATUS_COMPLETED)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt)
        ;

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
                ->setCreatedAt($patrol->getCreatedAt())
                ->setUpdatedAt($patrol->getUpdatedAt())
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
//            if ($isLeader) {
//                // Validates leader grade - disabled
//                    if ($member->getGradeId() == RegistrationLists::GRADE_NO) {
//                        $memberView->get('gradeId')
//                            ->addError(new FormError($translator->trans('form.error.grade_inproper')));
//                    }
//            }
        }
        $leader = $patrol->getLeader();
        if (isset($leader)) {
            $patrol->setActivationHash($this->generateActivationHash($leader->getEmail()));
        }

        return $this;
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
            ->setBody($this->renderView($emailView, $emailParams), 'text/html')
        ;

        $mailer = $this->get('mailer');
        if (!$mailer->send($message)) {
            throw new RegistrationException('form.exception.email');
        }
    }

    /**
     * Confirm participant
     *
     * @param Participant|null $participant participant
     *
     * @return self
     *
     * @throws RegistrationException
     */
    private function confirmParticipant(Participant $participant = null)
    {
        if (!isset($participant) || !$participant->isCompleted() || $participant->isConfirmed()) {
            throw new RegistrationException();
        }
        $participant->setStatus(Participant::STATUS_CONFIRMED);
        $this
            ->get('jambo_bundle.repository.participant')
            ->update($participant, true)
        ;

        return $this;
    }

    /**
     * Confirm patrol
     *
     * @param Patrol|null $patrol patrol
     *
     * @return self
     *
     * @throws RegistrationException
     */
    private function confirmPatrol(Patrol $patrol = null)
    {
        if (!isset($patrol) || !$patrol->isCompleted() || $patrol->isConfirmed()) {
            throw new RegistrationException();
        }
        $patrol->setStatus(Patrol::STATUS_CONFIRMED);
        $this
            ->get('jambo_bundle.repository.patrol')
            ->update($patrol, true)
        ;
        foreach ($patrol->getMembers() as $participant) {
            $this->confirmParticipant($participant);
        }

        return $this;
    }

    /**
     * Confirm troop
     *
     * @param Troop|null $troop troop
     *
     * @return self
     *
     * @throws RegistrationException
     */
    private function confirmTroop(Troop $troop = null)
    {
        if (!isset($troop) || !$troop->isCompleted() || $troop->isConfirmed()) {
            throw new RegistrationException();
        }
        $troop->setStatus(Troop::STATUS_CONFIRMED);
        $this
            ->get('jambo_bundle.repository.troop')
            ->update($troop, true)
        ;
        foreach ($troop->getPatrols() as $patrol) {
            $this->confirmPatrol($patrol);
        }

        return $this;
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

            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $this->getFormName($error->getOrigin()) . ': ' . $error->getMessage();
            }
            $this
                ->get('logger')
                ->warning(sprintf('Errors in form "%s": %s', $form->getName(), json_encode($errors)))
            ;
        }
    }

    /**
     * Get form name
     *
     * @param FormInterface $form form
     *
     * @return string
     */
    private function getFormName(FormInterface $form)
    {
        $nameParts = [];
        $parentForm = $form->getParent();
        if ($parentForm) {
            $nameParts[] = $this->getFormName($parentForm);
        }
        $nameParts[] = $form
            ->getName()
        ;
        $name = implode('.', $nameParts);

        return $name;
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
        $timeLimits = $this->getParameter('jambo.time_limits');
        $timeLimit = new DateTime($timeLimits['registration']);
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

    /**
     * Patrol participants limits exceeded
     *
     * @return bool
     */
    private function patrolParticipantsLimitsExceeded()
    {
        $timeLimits = $this->getParameter('jambo.time_limits');
        $timeLimit = new DateTime($timeLimits['patrol_participants']);
        if (new DateTime('now') > $timeLimit) {
            return true;
        }

        return false;
    }
}
