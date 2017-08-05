<?php

namespace JamboBundle\Model;

/**
 * Model
 */
class Action
{
    use RecordTrait;

    /** @const string */
    const TYPE_UPDATE_PARTICIPANT_DATA = 'update_participant_data';

    /** @const string */
    const TYPE_UPDATE_TROOP_DATA = 'update_troop_data';

    /** @var User */
    protected $user;

    /** @var string */
    protected $type;

    /** @var int|null */
    protected $objectId;

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param User $user user
     *
     * @return self
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get object ID
     *
     * @return int|null
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set object ID
     *
     * @param int|null $objectId object ID
     *
     * @return self
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }
}
