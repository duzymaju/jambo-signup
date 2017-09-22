<?php

namespace JamboBundle\Entity\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use JamboBundle\Entity\EntityInterface;

/**
 * Repository
 */
interface BaseRepositoryInterface extends ObjectRepository
{
    /**
     * Insert
     *
     * @param EntityInterface $entity entity
     * @param bool            $flush  flag, if flush should be done?
     *
     * @return self
     */
    public function insert(EntityInterface $entity, $flush = false);

    /**
     * Update
     *
     * @param EntityInterface $entity entity
     * @param bool            $flush  flag, if flush should be done?
     *
     * @return self
     */
    public function update(EntityInterface $entity, $flush = false);

    /**
     * Delete
     *
     * @param EntityInterface $entity entity
     * @param bool            $flush  flag, if flush should be done?
     *
     * @return self
     */
    public function delete(EntityInterface $entity, $flush = false);

    /**
     * Flush
     *
     * @return self
     */
    public function flush();
}
