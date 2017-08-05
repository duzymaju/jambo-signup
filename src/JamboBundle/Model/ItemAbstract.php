<?php

namespace JamboBundle\Model;

/**
 * Model
 */
class ItemAbstract
{
    use RecordTrait;

    /** @var int|null */
    protected $districtId;

    /** @var string */
    protected $comments;

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
}
