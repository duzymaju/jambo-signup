<?php

namespace JamboBundle\Model;

/**
 * Model
 */
interface StatusAwareInterface
{
    /** @const int */
    const STATUS_CONFIRMED = 1;

    /** @const int */
    const STATUS_PAYED = 2;

    /** @const int */
    const STATUS_RESIGNED = 6;

    /** @const int */
    const STATUS_NOT_CONFIRMED = 0;

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status status
     *
     * @return self
     */
    public function setStatus($status);

    /**
     * Is confirmed
     *
     * @return bool
     */
    public function isConfirmed();

    /**
     * Is payed
     *
     * @return bool
     */
    public function isPayed();

    /**
     * Is resigned
     *
     * @return bool
     */
    public function isResigned();

    /**
     * Get activation hash
     *
     * @return string
     */
    public function getActivationHash();

    /**
     * Set activation hash
     *
     * @param string $activationHash activation hash
     *
     * @return self
     */
    public function setActivationHash($activationHash);
}
