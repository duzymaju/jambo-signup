<?php

namespace JamboBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Model
 */
class Troop implements StatusAwareInterface
{
    use RecordTrait;
    use StatusAwareTrait;

    /** @var string */
    protected $name;

    /** @var int|null */
    protected $districtId;

    /** @var string */
    protected $comments;

    /** @var Participant */
    protected $leader;

    /** @var ArrayCollection */
    protected $patrols;

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
     * Get patrols
     *
     * @return ArrayCollection
     */
    public function getPatrols()
    {
        return $this->patrols;
    }

    /**
     * Add patrol
     *
     * @param Patrol $patrol patrol
     *
     * @return self
     */
    public function addPatrol(Patrol $patrol)
    {
        if (!$this->patrols->contains($patrol)) {
            $this->patrols->add($patrol);
        }

        return $this;
    }

    /**
     * Remove patrol
     *
     * @param Patrol $patrol patrol
     *
     * @return self
     */
    public function removePatrol(Patrol $patrol)
    {
        if ($this->patrols->contains($patrol)) {
            $this->patrols->removeElement($patrol);
        }

        return $this;
    }

    /**
     * Set patrols
     *
     * @param ArrayCollection $patrols patrols
     *
     * @return self
     */
    public function setPatrols(ArrayCollection $patrols)
    {
        $this->patrols = $patrols;

        return $this;
    }

    /**
     * Count patrols
     *
     * @return int
     */
    public function countPatrols()
    {
        return $this->patrols->count();
    }

    /**
     * Get members
     *
     * @return ArrayCollection
     */
    public function getMembers()
    {
        $members = new ArrayCollection();
        /** @var Patrol $patrol */
        foreach ($this->patrols as $patrol) {
            /** @var Participant $member */
            foreach ($patrol->getMembers() as $member) {
                $members->add($member);
            }
        }

        return $members;
    }

    /**
     * Initialize collections
     */
    public function initializeCollections()
    {
        if (!($this->patrols instanceof Collection)) {
            $this->patrols = new ArrayCollection();
        }
    }
}
