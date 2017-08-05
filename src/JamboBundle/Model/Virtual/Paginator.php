<?php

namespace JamboBundle\Model\Virtual;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * Model virtual
 */
class Paginator extends DoctrinePaginator
{
    /** @var int */
    private $pageNo;

    /** @var int */
    private $packSize;

    /** @var int */
    private $routeName;

    /** @var int */
    private $pageAttr;

    /** @var int */
    private $routeParams;

    /**
     * Constructor
     *
     * @param Query|QueryBuilder $query               query
     * @param int                $pageNo              page no
     * @param int                $packSize            pack size
     * @param bool               $fetchJoinCollection fetch join collection
     */
    public function __construct($query, $pageNo, $packSize, $fetchJoinCollection = true)
    {
        $this->pagesAround = 3;
        $this->pageNo = (int) $pageNo;
        $this->packSize = (int) $packSize;
        $this->routeName = '';
        $this->pageAttr = 'pageNo';
        $this->routeParams = [];

        parent::__construct($query, $fetchJoinCollection);
    }

    /**
     * Set pages around
     *
     * @param int $pagesAround pages around
     *
     * @return self
     */
    public function setPagesAround($pagesAround)
    {
        $this->pagesAround = (int) $pagesAround;

        return $this;
    }

    /**
     * Get pages around
     *
     * @return int
     */
    public function getPagesAround()
    {
        return $this->pagesAround;
    }

    /**
     * Get page no
     *
     * @return int
     */
    public function getPageNo()
    {
        return $this->pageNo;
    }

    /**
     * Get pages number
     *
     * @return int
     */
    public function getPagesNumber()
    {
        $pagesNumber = ceil($this->count() / $this->getPackSize());

        return $pagesNumber;
    }

    /**
     * Get pack size
     *
     * @return int
     */
    public function getPackSize()
    {
        return $this->packSize;
    }

    /**
     * Get first
     *
     * @return int|null
     */
    public function getFirst()
    {
        $pageNo = $this->getPageNo() - $this->getPagesAround() > 1 ? 1 : null;

        return $pageNo;
    }

    /**
     * Get prev
     *
     * @return int|null
     */
    public function getPrev()
    {
        $pageNo = $this->getPageNo() > 1 ? $this->getPageNo() - 1 : null;

        return $pageNo;
    }

    /**
     * Get next
     *
     * @return int|null
     */
    public function getNext()
    {
        $pageNo = $this->getPageNo() < $this->getPagesNumber() ? $this->getPageNo() + 1 : null;

        return $pageNo;
    }

    /**
     * Get last
     *
     * @return int|null
     */
    public function getLast()
    {
        $pageNo =
            $this->getPageNo() + $this->getPagesAround() < $this->getPagesNumber() ? $this->getPagesNumber() : null;

        return $pageNo;
    }

    /**
     * Is space after first
     *
     * @return bool
     */
    public function isSpaceAfterFirst()
    {
        $isSpace = $this->getPageNo() - $this->getPagesAround() - 1 > 1;

        return $isSpace;
    }

    /**
     * Is space before last
     *
     * @return bool
     */
    public function isSpaceBeforeLast()
    {
        $isSpace = $this->getPageNo() + $this->getPagesAround() + 1 < $this->getPagesNumber();

        return $isSpace;
    }

    /**
     * Get pages list
     *
     * @return array
     */
    public function getPagesList()
    {
        $startNo = max($this->getPageNo() - $this->getPagesAround(), 1);
        $finishNo = min($this->getPageNo() + $this->getPagesAround(), $this->getPagesNumber());
        $pagesList = range($startNo, $finishNo);

        return $pagesList;
    }

    /**
     * Set route name
     *
     * @param string $routeName route name
     *
     * @return self
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * Get route name
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Set page attribute name
     *
     * @param string $pageAttr page attribute name
     *
     * @return self
     */
    public function setPageAttr($pageAttr)
    {
        $this->pageAttr = $pageAttr;

        return $this;
    }

    /**
     * Get page attribute name
     *
     * @return string
     */
    public function getPageAttr()
    {
        return $this->pageAttr;
    }

    /**
     * Set route params
     *
     * @param array $routeParams route params
     *
     * @return self
     */
    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * Get route params
     *
     * @param int $pageNo page no
     *
     * @return array
     */
    public function getRouteParams($pageNo)
    {
        $routeParams = array_merge($this->routeParams, [
            $this->getPageAttr() => $pageNo,
        ]);

        return $routeParams;
    }
}
