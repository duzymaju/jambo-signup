<?php

namespace JamboBundle\Entity\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use JamboBundle\Entity\Participant;
use JamboBundle\Form\RegistrationLists;

/**
 * Repository
 */
class ParticipantRepository extends EntityRepository implements BaseRepositoryInterface, SearchRepositoryInterface
{
    use BaseRepositoryTrait;

    /** @var RegistrationLists */
    private $registrationLists;

    /** @var int */
    private $totalNumber;

    /**
     * Set registration lists
     *
     * @param RegistrationLists $registrationLists registration lists
     * 
     * @return self
     */
    public function setRegistrationLists(RegistrationLists $registrationLists)
    {
        $this->registrationLists = $registrationLists;

        return $this;
    }

    /**
     * Get full info by
     *
     * @param array $criteria criteria
     * @param array $orderBy  order by
     *
     * @return array
     */
    public function getFullInfoBy(array $criteria = [], array $orderBy = [])
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p, t')
            ->leftJoin('p.troop', 't');
        foreach ($criteria as $column => $value) {
            $columnParts = explode('.', $column);
            $sign = count($columnParts) == 2 ? array_pop($columnParts) : null;
            $column = array_shift($columnParts);
            if (is_array($value)) {
                $condition = 'p.' . $column . ($sign == 'not' ? ' NOT' : '') . ' IN (:' . $column . ')';
            } elseif ($sign == 'lt') {
                $condition = 'p.' . $column . ' < :' . $column;
            } elseif ($sign == 'gt') {
                $condition = 'p.' . $column . ' > :' . $column;
            } elseif ($sign == 'lte') {
                $condition = 'p.' . $column . ' <= :' . $column;
            } elseif ($sign == 'gte') {
                $condition = 'p.' . $column . ' >= :' . $column;
            } else {
                $condition = 'p.' . $column . ' = :' . $column;
            }
            $qb->andWhere($condition)
                ->setParameter($column, $value);
        }
        foreach ($orderBy as $column => $direction) {
            $qb->addOrderBy('p.' . $column, $direction);
        }
        $results = $qb->getQuery()
            ->getResult();

        return $results;
    }

    /**
     * Get all ordered by
     *
     * @param array $orderBy order by
     *
     * @return array
     */
    public function getAllOrderedBy(array $orderBy = [])
    {
        $results = $this->getFullInfoBy([], $orderBy);

        return $results;
    }

    /**
     * Get by time
     * 
     * @param DateTime $timeFrom time from
     * @param DateTime $timeTo   time to
     *
     * @return array
     */
    public function getByTime(DateTime $timeFrom, DateTime $timeTo)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p, t')
            ->leftJoin('p.troop', 't')
            ->andWhere('p.createdAt BETWEEN :timeFrom AND :timeTo')
            ->orderBy('p.troop', 'ASC')
            ->setParameter('timeFrom', $timeFrom->format('Y-m-d'))
            ->setParameter('timeTo', $timeTo->format('Y-m-d'));
        $results = $qb->getQuery()
            ->getResult();

        return $results;
    }

    /**
     * Get total number
     *
     * @param bool $refresh  refresh
     * @param bool $forceAll force all
     *
     * @return int
     */
    public function getTotalNumber($refresh = false, $forceAll = false)
    {
        if (!isset($this->totalNumber) || $refresh) {
            $qb = $this
                ->getEntityManager()
                ->createQueryBuilder()
            ;
            $qb
                ->select('count(p.id)')
                ->from('JamboBundle:Participant', 'p')
            ;
            if (!$forceAll) {
                $qb
                    ->andWhere('p.status >= :statusFrom')
                    ->andWhere('p.status < :statusTo')
                    ->setParameter('status', Participant::STATUS_CONFIRMED)
                    ->setParameter('status', Participant::STATUS_RESIGNED)
                ;
            }

            $this->totalNumber = $qb
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        return $this->totalNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function searchBy(array $queries)
    {
        $qb = $this->createQueryBuilder('p');

        $i = 1;
        foreach ($queries as $query) {
            if (!is_numeric($query)) {
                $qb->orWhere('p.firstName LIKE :firstName_' .$i)
                    ->setParameter('firstName_' .$i, '%' . $query . '%');

                $qb->orWhere('p.lastName LIKE :lastName_' .$i)
                    ->setParameter('lastName_' .$i, '%' . $query . '%');
            } else {
                $queryInteger = (int) $query;
                if ($queryInteger > 0) {
                    $qb->orWhere('p.pesel LIKE :pesel_' .$i)
                        ->setParameter('pesel_' .$i, (int) $query);
                }
            }

            $qb->orWhere('p.address LIKE :address_' .$i)
                ->setParameter('address_' .$i, '%' . $query . '%');

            $qb->orWhere('p.phone LIKE :phone_' .$i)
                ->setParameter('phone_' .$i, '%' . $query . '%');

            $qb->orWhere('p.email LIKE :email_' .$i)
                ->setParameter('email_' .$i, $query);

            $i++;
        }
        $results = $qb->getQuery()
            ->getResult();

        return $results;
    }
}
