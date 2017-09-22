<?php

namespace JamboBundle\Controller\Admin;

use DateTime;
use JamboBundle\Entity\Participant;
use JamboBundle\Entity\Repository\ParticipantRepository;
use JamboBundle\Exception\ExceptionInterface;
use JamboBundle\Form\Type\ParticipantEditType;
use JamboBundle\Model\Action;
use JamboBundle\Model\Virtual\Paginator;
use JamboBundle\Twig\JamboExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Admin controller
 */
class ParticipantController extends AbstractController
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
        ];
        $criteria = $this->getCriteria($request->query, $criteriaSettings);

        $orderBy = [
            'createdAt' => 'DESC',
        ];

        /** @var Paginator $participants */
        $participants = $this->getRepository()
            ->getPackOrException($pageNo, $this->getParameter('jambo.admin.pack_size'), $criteria, $orderBy, [
                'p' => 'patrol',
            ]);

        return $this->render('JamboBundle::admin/participant/index.html.twig', [
            'criteria' => $criteria,
            'participants' => $participants->setRouteName('admin_participant_index'),
        ]);
    }

    /**
     * Show action
     *
     * @param int $id ID
     *
     * @return Response
     */
    public function showAction($id)
    {
        /** @var Participant $participant */
        $participant = $this->getRepository()
            ->findOneByOrException([
                'id' => $id,
            ]);

        $response = $this->render('JamboBundle::admin/participant/show.html.twig', [
            'ageLimit' => $this->getParameter('jambo.age_limit.date'),
            'participant' => $participant,
        ]);

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
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $this->get('jambo_bundle.repository.participant');
        /** @var Participant $participant */
        $participant = $participantRepository->findOneByOrException([
            'id' => $id,
        ]);

        $form = $this->createForm(ParticipantEditType::class, $participant, [
            'action' => $this->generateUrl('admin_participant_edit', [
                'id' => $id,
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $participant->setUpdatedAt(new DateTime());
                $participantRepository->update($participant, true);

                $this
                    ->get('jambo_bundle.manager.action')
                    ->log(Action::TYPE_UPDATE_PARTICIPANT_DATA, $participant->getId(), $this->getUser())
                ;

                $this->addMessage('admin.edit.success', 'success');
                $response = $this->softRedirect($this->generateUrl('admin_participant_show', [
                    'id' => $id,
                ]));
            } catch (ExceptionInterface $e) {
                unset($e);
                $this->addMessage('form.exception.database', 'error');
            }
        }
        if (!isset($response)) {
            $this->addErrorMessage($form);
            $response = $this->render('JamboBundle::admin/participant/edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $response;
    }

    /**
     * List action
     *
     * @param Request $request request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        /** @var JamboExtension $filters */
        $filters = $this->get('jambo_bundle.twig_extension.jambo');
        $participantsRepository = $this->getRepository();

        $type = $request->query->get('type');
        $adultAge = $this->getParameter('jambo.age_limit.adult');
        $date = new DateTime($this->getParameter('jambo.age_limit.date'));
        $adultDate = $date->modify(sprintf('-%s years', $adultAge));
        $adultDateString = $adultDate->format('Y-m-d');

        $orderBy = [
            'createdAt' => 'DESC',
        ];
        switch ($type) {
            case 'adults':
                $participants = $participantsRepository->getFullInfoBy([
                    'birthDate.lt' => $adultDateString,
                ], $orderBy);
                break;

            case 'children':
                $participants = $participantsRepository->getFullInfoBy([
                    'birthDate.gte' => $adultDateString,
                ], $orderBy);
                $type = 'children';
                break;

            case 'all':
            default:
                $participants = $participantsRepository->getAllOrderedBy($orderBy);
                $type = null;
        }

        $showPesel = (bool) $request->query->get('showPesel');

        $data = [];
        $data[] = [
            $translator->trans('form.id'),
            $translator->trans('form.status'),
            $translator->trans('form.first_name'),
            $translator->trans('form.last_name'),
            $translator->trans('form.address'),
            $translator->trans('form.phone'),
            $translator->trans('form.email'),
            $translator->trans('form.birth_date'),
            $translator->trans('admin.age_at_limit', [
                '%date%' => $this->getParameter('jambo.age_limit.date'),
            ]),
            $translator->trans('form.sex'),
            $translator->trans('form.grade'),
            $translator->trans('form.district'),
            $translator->trans('form.pesel'),
            $translator->trans('form.father_name'),
            $translator->trans('form.emergency_info'),
            $translator->trans('form.emergency_phone'),
            $translator->trans('form.shirt_size'),
            $translator->trans('form.comments'),
            $translator->trans('form.troop_name'),
            $translator->trans('form.patrol_name'),
            $translator->trans('admin.created_at'),
        ];
        foreach ($participants as $participant) {
            /** @var Participant $participant */
            $data[] = [
                $participant->getId(),
                $filters->statusNameFilter($participant->getStatus()),
                $participant->getFirstName(),
                $participant->getLastName(),
                $participant->getAddress(),
                $participant->getPhone(),
                $participant->getEmail(),
                $participant->getBirthDate()
                    ->format('Y-m-d'),
                $filters->ageAtLimitFilter($participant->getBirthDate()),
                $filters->sexNameFilter($participant->getSex()),
                $participant->getGradeId() > 0 ? $filters->gradeNameFilter($participant->getGradeId()) : '-',
                $participant->getDistrictId() > 0 ? $filters->districtNameFilter($participant->getDistrictId()) : '-',
                $participant->getPesel() > 0 ? $filters->peselModifyFilter($participant->getPesel(), $showPesel) : '-',
                $participant->getFatherName() ? $participant->getFatherName() : '-',
                $participant->getEmergencyInfo() ? $participant->getEmergencyInfo() : '-',
                $participant->getEmergencyPhone() ? $participant->getEmergencyPhone() : '-',
                $participant->getShirtSize() > 0 ? $filters->shirtSizeNameFilter($participant->getShirtSize()) : '-',
                empty($participant->getComments()) ? '-' : $participant->getComments(),
                $participant->getTroop() ? $participant->getTroop()
                    ->getName() : '-',
                $participant->getPatrol() ? $participant->getPatrol()
                    ->getName() : '-',
                $participant->getCreatedAt()
                    ->format('Y-m-d'),
            ];
        }

        return $this->getCsvResponse($data, 'participant_list' . (empty($type) ? '' : '_' . $type));
    }

    /**
     * Get repository
     *
     * @return ParticipantRepository
     */
    private function getRepository()
    {
        /** @var ParticipantRepository $repository */
        $repository = $this->get('jambo_bundle.repository.participant');

        return $repository;
    }
}
