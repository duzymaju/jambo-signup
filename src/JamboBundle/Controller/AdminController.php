<?php

namespace JamboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller
 */
class AdminController extends Controller
{
    /**
     * Index action
     *
     * @return Response
     */
    public function indexAction()
    {
        $criteria = [];
        $orderBy = [
            'createdAt' => 'DESC',
        ];
        $limit = 5;

        $participantRepository = $this->get('jambo_bundle.repository.participant');
        $troopRepository = $this->get('jambo_bundle.repository.troop');

        return $this->render('JamboBundle::admin/index.html.twig', [
            'limit' => $limit,
            'lists' => [
                'participants' => [
                    'counter' => 'admin.participants.counter',
                    'items' => $participantRepository->findBy($criteria, $orderBy, $limit),
                    'routeIndex' => 'admin_participant_index',
                    'routeShow' => 'admin_participant_show',
                    'title' => 'admin.participants',
                    'totalNumber' => $participantRepository->getTotalNumber(false, true),
                ],
                'troops' => [
                    'counter' => 'admin.troops.counter',
                    'items' => $troopRepository->findBy($criteria, $orderBy, $limit),
                    'routeIndex' => 'admin_troop_index',
                    'routeShow' => 'admin_troop_show',
                    'title' => 'admin.troops',
                    'totalNumber' => $troopRepository->getTotalNumber(true),
                ],
            ],
        ]);
    }

    /**
     * Main action
     *
     * @return Response
     */
    public function mainAction()
    {
        return $this->redirect($this->generateUrl('admin_index'));
    }
}
