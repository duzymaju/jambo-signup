<?php

namespace JamboBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Model
 */
interface BandInterface
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get leader
     *
     * @return PersonInterface
     */
    public function getLeader();

    /**
     * Get members
     *
     * @return ArrayCollection
     */
    public function getMembers();
}
