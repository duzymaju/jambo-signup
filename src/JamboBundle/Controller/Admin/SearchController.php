<?php

namespace JamboBundle\Controller\Admin;

use Exception;
use JamboBundle\Entity\Repository\SearchRepositoryInterface;
use JamboBundle\Form\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin controller
 */
class SearchController extends AbstractController
{
    /**
     * Index action
     *
     * @param Request $request request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(SearchType::class, null, [
            'action' => $this->generateUrl('admin_search_index'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $form->get('type')
                ->getData();
            $query = $form->get('query')
                ->getData();

            $queries = [];
            foreach (explode(' ', $query) as $element) {
                if  (!empty($element)) {
                    $queries[] = $element;
                }
            }

            if ($type == SearchType::CHOICE_ALL) {
                $results = [
                    SearchType::CHOICE_PARTICIPANT => $this->getRepository(SearchType::CHOICE_PARTICIPANT)
                        ->searchBy($queries),
                    SearchType::CHOICE_TROOP => $this->getRepository(SearchType::CHOICE_TROOP)
                        ->searchBy($queries),
                ];
            } else {
                $results = [
                    $type => $this->getRepository($type)
                        ->searchBy($queries),
                ];
            }
        } else {
            $query = null;
            $results = null;
        }

        return $this->render('JamboBundle::admin/search.html.twig', [
            'form' => $form->createView(),
            'query' => $query,
            'results' => $results,
        ]);
    }

    /**
     * Get repository
     *
     * @param string $type type
     *
     * @return SearchRepositoryInterface
     *
     * @throws Exception
     */
    private function getRepository($type)
    {
        switch ($type) {
            case SearchType::CHOICE_PARTICIPANT:
                $serviceName = 'jambo_bundle.repository.participant';
                break;

            case SearchType::CHOICE_TROOP:
                $serviceName = 'jambo_bundle.repository.troop';
                break;

            default:
                throw new Exception('There is no proper repository service name defined.');
        }
        /** @var SearchRepositoryInterface $repository */
        $repository = $this->get($serviceName);

        return $repository;
    }
}
