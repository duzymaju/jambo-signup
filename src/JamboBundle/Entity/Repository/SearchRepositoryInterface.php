<?php

namespace JamboBundle\Entity\Repository;

/**
 * Repository
 */
interface SearchRepositoryInterface
{
    /**
     * Search by
     *
     * @param array $queries queries
     *
     * @return self
     */
    public function searchBy(array $queries);
}
