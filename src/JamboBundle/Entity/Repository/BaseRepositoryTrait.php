<?php

namespace JamboBundle\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use JamboBundle\Entity\EntityInterface;
use JamboBundle\Model\Virtual\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Repository
 */
trait BaseRepositoryTrait
{
    /**
     * Find one by or throw NotFoundHttpException
     *
     * @param array      $criteria criteria
     * @param array|null $orderBy  order by
     *
     * @return object
     *
     * @throws NotFoundHttpException
     */
    public function findOneByOrException(array $criteria, array $orderBy = null)
    {
        $result = $this->findOneBy($criteria, $orderBy);
        if (!isset($result)) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    /**
     * Insert
     *
     * @param EntityInterface $entity entity
     * @param bool            $flush  flag, if flush should be done?
     *
     * @return self
     */
    public function insert(EntityInterface $entity, $flush = false)
    {
        return $this->save($entity, $flush);
    }

    /**
     * Update
     *
     * @param EntityInterface $entity entity
     * @param bool            $flush  flag, if flush should be done?
     *
     * @return self
     */
    public function update(EntityInterface $entity, $flush = false)
    {
        return $this->save($entity, $flush);
    }

    /**
     * Delete
     *
     * @param EntityInterface $entity entity
     * @param bool            $flush  flag, if flush should be done?
     *
     * @return self
     */
    public function delete(EntityInterface $entity, $flush = false)
    {
        $this->getEntityManager()
            ->remove($entity);
        if ($flush) {
            $this->flush();
        }
        
        return $this;
    }

    /**
     * Flush
     *
     * @return self
     */
    public function flush()
    {
        $this->getEntityManager()
            ->flush();

        return $this;
    }
    
    /**
     * Save
     *
     * @param EntityInterface $entity entity
     * @param bool            $flush  flag, if flush should be done?
     *
     * @return self
     */
    protected function save(EntityInterface $entity, $flush = false)
    {
        $this->getEntityManager()
            ->persist($entity);
        if ($flush) {
            $this->flush();
        }
        return $this;
    }

    /**
     * Get pack
     *
     * @param int   $pageNo    page no
     * @param int   $packSize  pack size
     * @param array $criteria  criteria
     * @param array $orderBy   order by
     * @param array $leftJoins left joins
     *
     * @return Paginator
     */
    public function getPack($pageNo, $packSize, array $criteria = [], array $orderBy = [], array $leftJoins = [])
    {
        $alias = 'xt';
        $selection = $alias;
        $qb = $this->createQueryBuilder($alias);
        foreach ($leftJoins as $joinedAlias => $joinedColumn) {
            $qb->leftJoin((strpos($joinedColumn, '.') === false ? $alias . '.' : '') . $joinedColumn, $joinedAlias);
            $selection .= ', ' . $joinedAlias;
        }
        $qb->select($selection);
        foreach ($criteria as $column => $value) {
            $qb->andWhere($alias . '.' . $column . ' = :' . $column)
                ->setParameter($column, $value);
        }
        foreach ($orderBy as $column => $direction) {
            $qb->addOrderBy($alias . '.' . $column, $direction);
        }
        $query = $qb->getQuery()
            ->setFirstResult($packSize * ($pageNo - 1))
            ->setMaxResults($packSize);

        $paginator = new Paginator($query, $pageNo, $packSize);

        return $paginator;
    }

    /**
     * Get pack or throw NotFoundHttpException
     *
     * @param int   $pageNo    page no
     * @param int   $packSize  pack size
     * @param array $criteria  criteria
     * @param array $orderBy   order by
     * @param array $leftJoins left joins
     *
     * @return Paginator
     *
     * @throws NotFoundHttpException
     */
    public function getPackOrException($pageNo, $packSize, array $criteria = [], array $orderBy = [],
        array $leftJoins = [])
    {
        $result = $this->getPack($pageNo, $packSize, $criteria, $orderBy, $leftJoins);
        if ($pageNo > 1 && $result->getIterator()->count() == 0) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    /**
     * Get entity manager
     *
     * @return EntityManager
     */
    abstract public function getEntityManager();

    /**
     * Create query builder
     *
     * @param string      $alias   alias
     * @param string|null $indexBy index by
     *
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);
}
