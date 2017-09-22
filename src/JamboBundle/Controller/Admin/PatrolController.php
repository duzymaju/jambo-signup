<?php

namespace JamboBundle\Controller\Admin;

use DateTime;
use JamboBundle\Entity\Patrol;
use JamboBundle\Entity\Repository\PatrolRepository;
use JamboBundle\Exception\EditFormException;
use JamboBundle\Exception\ExceptionInterface;
use JamboBundle\Form\Type\PatrolEditType;
use JamboBundle\Model\Action;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin controller
 */
class PatrolController extends AbstractController
{
    /**
     * Show action
     *
     * @param int $id ID
     *
     * @return Response
     */
    public function showAction($id)
    {
        /** @var Patrol $patrol */
        $patrol = $this
            ->getRepository()
            ->findOneByOrException([
                'id' => $id,
            ])
        ;

        $response = $this->render('JamboBundle::admin/patrol/show.html.twig', [
            'patrol' => $patrol,
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
        $patrolRepository = $this->getRepository();
        /** @var Patrol $patrol */
        $patrol = $patrolRepository->findOneByOrException([
            'id' => $id,
        ]);

        $form = $this->createForm(PatrolEditType::class, $patrol, [
            'action' => $this->generateUrl('admin_patrol_edit', [
                'id' => $id,
            ]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $patrol->setUpdatedAt(new DateTime());
                $patrolRepository->update($patrol, true);

                $this
                    ->get('jambo_bundle.manager.action')
                    ->log(Action::TYPE_UPDATE_PATROL_DATA, $patrol->getId(), $this->getUser())
                ;

                $this->addMessage('admin.edit.success', 'success');
                $response = $this->softRedirect($this->generateUrl('admin_patrol_show', [
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
            $response = $this->render('JamboBundle::admin/patrol/edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $response;
    }

    /**
     * Get repository
     *
     * @return PatrolRepository
     */
    private function getRepository()
    {
        /** @var PatrolRepository $repository */
        $repository = $this->get('jambo_bundle.repository.patrol');

        return $repository;
    }
}
