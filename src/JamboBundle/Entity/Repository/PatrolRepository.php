<?php

namespace JamboBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository
 */
class PatrolRepository extends EntityRepository implements BaseRepositoryInterface, SearchRepositoryInterface
{
    use BaseRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function searchBy(array $queries)
    {
        $qb = $this->createQueryBuilder('t');

        $i = 1;
        foreach ($queries as $query) {
            $qb->orWhere('t.name LIKE :name_' .$i)
                ->setParameter('name_' .$i, '%' . $query . '%');

            $i++;
        }
        $results = $qb->getQuery()
            ->getResult();

        return $results;
    }
}
