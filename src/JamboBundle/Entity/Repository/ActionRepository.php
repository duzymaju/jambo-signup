<?php

namespace JamboBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository
 */
class ActionRepository extends EntityRepository implements BaseRepositoryInterface
{
    use BaseRepositoryTrait;
}
