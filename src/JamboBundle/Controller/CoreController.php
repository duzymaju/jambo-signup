<?php

namespace JamboBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller
 */
class CoreController extends Controller
{
    /**
     * Homepage action
     *
     * @return Response
     */
    public function homepageAction()
    {
        return $this->redirect($this->generateUrl('registration_index'));
    }
}
