<?php

namespace JamboBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Model
 */
class Patrol
{
    use RecordTrait;

    /** @var string */
    protected $name;

    /** @var int|null */
    protected $districtId;

    /** @var string */
    protected $comments;

    /** @var Troop */
    protected $troop;

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
     * Get district ID
     *
     * @return int|null
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * Set district ID
     *
     * @param int|null $districtId district ID
     *
     * @return self
     */
    public function setDistrictId($districtId = null)
    {
        $this->districtId = $districtId;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string|null
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set comments
     *
     * @param string|null $comments comments
     *
     * @return self
     */
    public function setComments($comments = null)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get troop
     *
     * @return Troop
     */
    public function getTroop()
    {
        return $this->troop;
    }

    /**
     * Set troop
     *
     * @param Troop $troop troop
     *
     * @return self
     */
    public function setTroop(Troop $troop)
    {
        $this->troop = $troop;

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
