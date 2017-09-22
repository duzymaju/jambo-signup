<?php

namespace JamboBundle\Controller\Admin;

use DateTime;
use JamboBundle\Entity\Repository\TroopRepository;
use JamboBundle\Entity\Troop;
use JamboBundle\Exception\EditFormException;
use JamboBundle\Exception\ExceptionInterface;
use JamboBundle\Form\Type\TroopEditType;
use JamboBundle\Model\Action;
use JamboBundle\Model\Virtual\Paginator;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Admin controller
 */
class TroopController extends AbstractController
{
    /**
     * Index action
     *
     * @param Request $request request
     * @param int     $pageNo  page no
     *
     * @return Response
     */
    public function indexAction(Request $request, $pageNo)
    {
        $criteriaSettings = [
            'districtId' => 'getDistrict',
            'status' => [
                'getter' => 'getStatus',
                'lowestValue' => 0,
            ],
        ];
        $criteria = $this->getCriteria($request->query, $criteriaSettings);

        /** @var Paginator $troops */
        $troops = $this->getRepository()
            ->getPackOrException($pageNo, $this->getParameter('jambo.admin.pack_size'), $criteria, [
                'createdAt' => 'DESC',
            ]);

        return $this->render('JamboBundle::admin/troop/index.html.twig', [
            'criteria' => $criteria,
            'troops' => $troops->setRouteName('admin_troop_index'),
        ]);
    }

    /**
     * Show action
     *
     * @param Request $request request
     * @param int     $id      ID
     *
     * @return Response
     */
    public function showAction(Request $request, $id)
    {
        /** @var Troop $troop */
        $troop = $this->getRepository()
            ->findOneByOrException([
                'id' => $id,
            ]);

        $response = $this->sendReminderIfRequested($troop, $request);

        if (!isset($response)) {
            $response = $this->render('JamboBundle::admin/troop/show.html.twig', [
                'ageLimit' => $this->getParameter('jambo.age_limit.date'),
                'isReminderSendingPossible' => $this->isReminderSendingPossible($troop),
                'troop' => $troop,
            ]);
        }

        return $response;
    }

    /**
     * Edit action
     *
     * @param Request $request request
     * @param int     $id      ID
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        /** @var TroopRepository $troopRepository */
        $troopRepository = $this->get('jambo_bundle.repository.troop');
        /** @var Troop $troop */
        $troop = $troopRepository->findOneByOrException([
            'id' => $id,
        ]);

        $form = $this->createForm(TroopEditType::class, $troop, [
            'action' => $this->generateUrl('admin_troop_edit', [
                'id' => $id,
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $troop->setUpdatedAt(new DateTime());
                $troopRepository->update($troop, true);

                $this
                    ->get('jambo_bundle.manager.action')
                    ->log(Action::TYPE_UPDATE_TROOP_DATA, $troop->getId(), $this->getUser())
                ;

                $this->addMessage('admin.edit.success', 'success');
                $response = $this->softRedirect($this->generateUrl('admin_troop_show', [
                    'id' => $id,
                ]));
            } catch (EditFormException $e) {
                $this->addMessage($e->getMessage(), 'error');
            } catch (ExceptionInterface $e) {
                unset($e);
                $this->addMessage('form.exception.database', 'error');
            }
        }
        if (!isset($response)) {
            $this->addErrorMessage($form);
            $response = $this->render('JamboBundle::admin/troop/edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $response;
    }

    /**
     * Send reminder if requested
     *
     * @param Troop   $troop   troop
     * @param Request $request request
     *
     * @return Response|null
     */
    private function sendReminderIfRequested(Troop $troop, Request $request)
    {
        if (!$troop->isConfirmed()) {
            $sendReminder = (bool) $request->query->get('sendReminder');
            if ($sendReminder && $troop->getUpdatedAt() < $this->getReminderDeadline()) {
                /** @var FlashBagInterface $sessionFlashBag */
                $sessionFlashBag = $this->get('session')
                    ->getFlashBag();

                $leader = $troop->getLeader();
                $email = $leader->getEmail();
                $sex = $leader->getSex();
                $locale = $this->getLocale($this->getParameter('jambo.mailer_locale'));
                $subject = $this->get('translator')
                    ->trans('email.title', [], null, $locale);

                $message = Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($this->getParameter('mailer_user'))
                    ->setTo($email)
                    ->setReplyTo($this->getParameter('jambo.email.reply_to'))
                    ->setBody($this->renderView('JamboBundle::admin/troop/email.html.twig', [
                        'confirmationUrl' => $this->generateUrl('registration_troop_confirm', [
                            '_locale' => $locale,
                            'hash' => $troop->getActivationHash(),
                        ], UrlGeneratorInterface::ABSOLUTE_URL),
                        'locale' => $locale,
                        'sex' => $sex,
                    ]), 'text/html')
                ;

                $mailer = $this->get('mailer');
                if ($mailer->send($message)) {
                    $troop->setUpdatedAt(new DateTime());
                    $this->getRepository()
                        ->update($troop, true);
                    $sessionFlashBag->add('success', 'admin.reminder.success');
                } else {
                    $sessionFlashBag->add('error', 'admin.reminder.error');
                }

                return $this->redirect($this->generateUrl($request->get('_route'), [
                    'id' => $troop->getId(),
                ]));
            }
        }

        return null;
    }

    /**
     * Is reminder sending possible
     *
     * @param Troop $troop troop
     *
     * @return bool
     */
    private function isReminderSendingPossible(Troop $troop)
    {
        $isPossible = !$troop->isConfirmed() && $troop->getUpdatedAt() < $this->getReminderDeadline();

        return $isPossible;
    }

    /**
     * Get reminder deadline
     *
     * @return DateTime
     */
    private function getReminderDeadline()
    {
        $reminderDeadline = (new DateTime())->modify('-1 month');

        return $reminderDeadline;
    }

    /**
     * Get repository
     *
     * @return TroopRepository
     */
    private function getRepository()
    {
        /** @var TroopRepository $repository */
        $repository = $this->get('jambo_bundle.repository.troop');

        return $repository;
    }
}
