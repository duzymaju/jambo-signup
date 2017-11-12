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
            ->select('count(p.id)')
            ->from('JamboBundle:Patrol', 'p')
        ;
        if (!$forceAll) {
            $qb
                ->andWhere('p.status >= :statusFrom')
                ->andWhere('p.status < :statusTo')
                ->setParameter('statusFrom', Troop::STATUS_CONFIRMED)
                ->setParameter('statusTo', Troop::STATUS_RESIGNED)
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
            $qb->orWhere('t.name LIKE :name_' .$i)
                ->setParameter('name_' .$i, '%' . $query . '%');

            $i++;
        }
        $results = $qb->getQuery()
            ->getResult();

        return $results;
    }
}
