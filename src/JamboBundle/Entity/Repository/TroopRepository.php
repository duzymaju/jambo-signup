<?php

namespace JamboBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository
 */
class TroopRepository extends EntityRepository implements BaseRepositoryInterface, SearchRepositoryInterface
{
    use BaseRepositoryTrait;

    /**
     * Get total number
     *
     * @return int
     */
    public function getTotalNumber()
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();
        $qb->select('count(t.id)');
        $qb->from('JamboBundle:Troop', 't');

        $count = $qb->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function searchBy(array $queries)
    {
        /** @var QueryBuilder $qb */
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
