<?php

namespace JamboBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository
 */
class UserRepository extends EntityRepository implements BaseRepositoryInterface
{
    use BaseRepositoryTrait;
}
