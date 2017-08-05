<?php

namespace JamboBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Model
 */
class Troop extends ItemAbstract implements BandInterface, StatusAwareInterface
{
    use StatusAwareTrait;

    /** @var string */
    protected $name;

    /** @var Participant */
    protected $leader;

    /** @var ArrayCollection */
    protected $members;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->initializeCollections();
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get leader
     *
     * @return Participant
     */
    public function getLeader()
    {
        return $this->leader;
    }

    /**
     * Set leader
     *
     * @param Participant $leader leader
     *
     * @return self
     */
    public function setLeader(Participant $leader)
    {
        $this->leader = $leader;

        return $this;
    }

    /**
     * Get members
     *
     * @return ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add member
     *
     * @param Participant $member member
     *
     * @return self
     */
    public function addMember(Participant $member)
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    /**
     * Remove member
     *
     * @param Participant $member member
     *
     * @return self
     */
    public function removeMember(Participant $member)
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
        }

        return $this;
    }

    /**
     * Set members
     *
     * @param ArrayCollection $members members
     *
     * @return self
     */
    public function setMembers(ArrayCollection $members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * Initialize collections
     */
    public function initializeCollections()
    {
        if (!($this->members instanceof Collection)) {
            $this->members = new ArrayCollection();
        }
    }
}
