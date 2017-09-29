<?php

namespace JamboBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use JamboBundle\Entity\Troop;

/**
 * Repository
 */
class TroopRepository extends EntityRepository implements BaseRepositoryInterface, SearchRepositoryInterface
{
    use BaseRepositoryTrait;

    /**
     * Get total number
     *
     * @param bool $forceAll force all
     *
     * @return int
     */
    public function getTotalNumber($forceAll = false)
    {
        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
        ;
        $qb
            ->select('count(t.id)')
            ->from('JamboBundle:Troop', 't')
        ;
        if (!$forceAll) {
            $qb
                ->andWhere('t.status >= :status')
                ->setParameter('status', Troop::STATUS_COMPLETED)
            ;
        }

        $count = (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function searchBy(array $queries)
    {
        $qb = $this->createQueryBuilder('t');

        $i = 1;
        foreach ($queries as $query) {
            $qb
                ->orWhere('t.name LIKE :name_' .$i)
                ->setParameter('name_' .$i, '%' . $query . '%')
            ;

            $i++;
        }
        $results = $qb
            ->getQuery()
            ->getResult()
        ;

        return $results;
    }
}
