<?php

namespace JamboBundle\Model;

/**
 * Model
 */
trait StatusAwareTrait
{
    /** @var int */
    protected $status;

    /** @var string */
    protected $activationHash;

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Is completed
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status >= StatusAwareInterface::STATUS_COMPLETED;
    }

    /**
     * Is confirmed
     *
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->status >= StatusAwareInterface::STATUS_CONFIRMED;
    }

    /**
     * Is payed
     *
     * @return bool
     */
    public function isPayed()
    {
        return $this->status >= StatusAwareInterface::STATUS_PAYED;
    }

    /**
     * Is resigned
     *
     * @return bool
     */
    public function isResigned()
    {
        return $this->status >= StatusAwareInterface::STATUS_RESIGNED;
    }

    /**
     * Get activation hash
     *
     * @return string
     */
    public function getActivationHash()
    {
        return $this->activationHash;
    }

    /**
     * Set activation hash
     *
     * @param string $activationHash activation hash
     *
     * @return self
     */
    public function setActivationHash($activationHash)
    {
        $this->activationHash = $activationHash;

        return $this;
    }
}
