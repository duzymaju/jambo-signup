<?php

namespace JamboBundle\Manager;

use DateTime;
use JamboBundle\Entity\Action;
use JamboBundle\Entity\Repository\ActionRepository;
use JamboBundle\Entity\User;

/**
 * Manager
 */
class ActionManager
{
    /** @var ActionRepository */
    private $actionRepository;

    /**
     * Set action repository
     *
     * @param ActionRepository $actionRepository action repository
     *
     * @return self
     */
    public function setActionRepository(ActionRepository $actionRepository)
    {
        $this->actionRepository = $actionRepository;

        return $this;
    }

    /**
     * Log
     *
     * @param string    $type     type
     * @param int|null  $objectId object ID
     * @param User|null $user     user
     *
     * @return self
     */
    public function log($type, $objectId = null, User $user = null)
    {
        $createdAt = new DateTime();
        $action = new Action();
        $action->setType($type)
            ->setObjectId($objectId)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt);
        if (isset($user)) {
            $action->setUser($user);
        }
        $this->actionRepository->insert($action, true);

        return $this;
    }
}
