<?php

namespace JamboBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use JamboBundle\Entity\Participant;
use JamboBundle\Entity\Repository\BaseRepositoryInterface;
use JamboBundle\Entity\Troop;
use JamboBundle\Exception\RegistrationException;
use JamboBundle\Form\Type\TroopType;
use JamboBundle\Model\PersonInterface;
use JamboBundle\Model\StatusAwareInterface;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
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
                'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
            ]);
        }

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $troop = new Troop();
        $leader = new Participant();
        $leader->setTroop($troop);
        $troop->setLeader($leader)
            ->addMember($leader);
        $troopMinSize = $this->getParameter('jambo.size_limit.troop_min');
        $troopMaxSize = $this->getParameter('jambo.size_limit.troop_max');
        for ($i = 1; $i < $troopMinSize; $i++) {
            $member = new Participant();
            $member->setTroop($troop);
            $troop->addMember($member);
        }
        $form = $this->createForm(TroopType::class, $troop, [
            'action' => $this->generateUrl('registration_troop_form'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $members = $troop->getMembers();
            if ($members->count() > $troopMaxSize) {
                $troop->setMembers(new ArrayCollection($members->slice(0, $troopMaxSize)));
            }
            unset($members);
            $hash = $this->generateActivationHash($leader->getEmail());
            $createdAt = new DateTime();
            $troop->setStatus(Troop::STATUS_NOT_CONFIRMED)
                ->setActivationHash($hash)
                ->setCreatedAt($createdAt)
                ->setUpdatedAt($createdAt);
            foreach ($troop->getMembers() as $i => $member) {
                /** @var Participant $member */
                $isLeader = $member === $troop->getLeader();
                // Copies data from form to each participant
                $member
                    ->setStatus(Participant::STATUS_NOT_CONFIRMED)
                    ->setActivationHash($this->generateActivationHash($member->getEmail()))
                    ->setTroop($troop)
                    ->setCreatedAt($createdAt)
                    ->setUpdatedAt($createdAt)
                    ->setDistrictId($troop->getDistrictId())
                    ->setSex($member->getSexFromPesel())
                ;

                /** @var FormInterface $memberView */
                $memberView = $form->get('members')->get($i);
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
                    $this->mailSendingProcedure($leader->getEmail(), 'registration_troop_confirm',
                        'JamboBundle::registration/troop/email.html.twig', $hash, $leader->getSex());

                    try {
                        $this->get('jambo_bundle.repository.troop')
                            ->insert($troop, true);
                        $this->get('jambo_bundle.repository.participant')
                            ->insert($leader, true);
                    } catch (Exception $e) {
                        throw new RegistrationException('form.exception.database', 0, $e);
                    }

                    $successMessage = $translator->trans('success.registration.message', [
                        '%email%' => $leader->getEmail(),
                    ]);
                    $this->addMessage($successMessage, 'success');
                    $response = $this->redirect($this->generateUrl('registration_success'));
                } catch (ExceptionInterface $e) {
                    $this->addMessage($e->getMessage(), 'error');
                }
            }
        }
        if (!isset($response)) {
            $this->addErrorMessage($form);
            $response = $this->render('JamboBundle::registration/troop/form.html.twig', [
                'form' => $form->createView(),
                'max_size' => $troopMaxSize,
                'min_age_member' => $this->getParameter('jambo.age_limit.min'),
                'min_size' => $troopMinSize,
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
        $response = $this->confirmationProcedurePerson($this->get('jambo_bundle.repository.participant'), $hash,
            Participant::STATUS_CONFIRMED);

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
        $response = $this->confirmationProcedureBand($this->get('jambo_bundle.repository.troop'),
            $this->get('jambo_bundle.repository.participant'), $hash, Troop::STATUS_CONFIRMED,
            Participant::STATUS_CONFIRMED);

        return $response;
    }

    /**
     * Mail sending procedure
     *
     * @param string      $email        e-mail
     * @param string      $confirmRoute confirm route
     * @param string      $emailView    email view
     * @param string      $hash         hash
     * @param string|null $sex          sex
     *
     * @throws RegistrationException
     */
    private function mailSendingProcedure($email, $confirmRoute, $emailView, $hash, $sex = null)
    {
        $translator = $this->get('translator');

        $message = Swift_Message::newInstance()
            ->setSubject($translator->trans('email.title'))
            ->setFrom($this->getParameter('mailer_user'))
            ->setTo($email)
            ->setReplyTo($this->getParameter('jambo.email.reply_to'))
            ->setBody($this->renderView($emailView, [
                'confirmationUrl' => $this->generateUrl($confirmRoute, [
                    'hash' => $hash,
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'sex' => $sex,
            ]), 'text/html');

        $mailer = $this->get('mailer');
        if (!$mailer->send($message)) {
            throw new RegistrationException('form.exception.email');
        }
    }

    /**
     * Confirmation procedure person
     *
     * @param BaseRepositoryInterface $repository repository
     * @param string                  $hash       hash
     * @param integer                 $status     status
     *
     * @return Response
     */
    protected function confirmationProcedurePerson(BaseRepositoryInterface $repository, $hash, $status)
    {
        /** @var StatusAwareInterface|null $person */
        $person = $repository->findOneBy(array(
            'activationHash' => $hash,
        ));

        if (!isset($person) || $person->isConfirmed()) {
            $this->addMessage('confirmation.error', 'error');
        } else {
            $person->setStatus($status);
            $repository->update($person, true);
            $this->addMessage('confirmation.success', 'success');
        }

        return $this->render('JamboBundle::registration/confirmation.html.twig', [
            'participantsLimitsExceeded' => $this->participantsLimitsExceeded(),
        ]);
    }

    /**
     * Confirmation procedure band
     *
     * @param BaseRepositoryInterface $bandRepository   band repository
     * @param BaseRepositoryInterface $personRepository person repository
     * @param string                  $hash             hash
     * @param int                     $bandStatus       band status
     * @param int                     $personStatus     person status
     *
     * @return Response
     */
    protected function confirmationProcedureBand(BaseRepositoryInterface $bandRepository,
        BaseRepositoryInterface $personRepository, $hash, $bandStatus, $personStatus)
    {
        /** @var StatusAwareInterface|null $band */
        $band = $bandRepository->findOneBy(array(
            'activationHash' => $hash,
        ));

        if (!isset($band) || $band->isConfirmed()) {
            $this->addMessage('confirmation.error', 'error');
        } else {
            $band->setStatus($bandStatus);
            $bandRepository->update($band, true);
            /** @var BandInterface $person */
            $person = $band->getLeader();
            if (!$person->isConfirmed()) {
                $person->setStatus($personStatus);
                $personRepository->update($person, true);
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
     * @param PersonInterface $person          person
     * @param FormInterface   $ageField        age field
     * @param string          $minAgeParamName min age param name
     */
    private function validateAge(PersonInterface $person, FormInterface $ageField, $minAgeParamName)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $ageMin = $this->getParameter($minAgeParamName);
        $ageMax = $this->getParameter('jambo.age_limit.max');
        $ageLimit = new DateTime($this->getParameter('jambo.age_limit.date'));

        $birthDate = $person->getBirthDate();
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
    private function generateActivationHash($email)
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
     * @return bool
     */
    private function participantsLimitsExceeded()
    {
        $timeLimit = new DateTime($this->getParameter('jambo.time_limit.participants'));
        if (new DateTime('now') > $timeLimit) {
            return true;
        }

        $numberTotalLimit = $this->getParameter('jambo.size_limit.total');
        $participantRepository = $this->get('jambo_bundle.repository.participant');
        if ($participantRepository->getTotalNumber() >= $numberTotalLimit) {
            return true;
        }

        return false;
    }
}
