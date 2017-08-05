<?php

namespace JamboBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * Model
 */
class User extends BaseUser
{
    /** @var ArrayCollection */
    protected $actions;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->initializeCollections();
    }

    /**
     * Get actions
     *
     * @return ArrayCollection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Add action
     *
     * @param Action $action action
     *
     * @return self
     */
    public function addAction(Action $action)
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
        }

        return $this;
    }

    /**
     * Remove action
     *
     * @param Action $action action
     *
     * @return self
     */
    public function removeAction(Action $action)
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
        }

        return $this;
    }

    /**
     * Set actions
     *
     * @param ArrayCollection $actions actions
     *
     * @return self
     */
    public function setActions(ArrayCollection $actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Initialize collections
     */
    public function initializeCollections()
    {
        if (!($this->actions instanceof Collection)) {
            $this->actions = new ArrayCollection();
        }
    }
}
