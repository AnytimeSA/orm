<?php

namespace DVE\EntityORM\QueryBuilder;

class MySqlQueryBuilder extends QueryBuilderAbstract
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string
     */
    private $from;

    /**
     * @var array
     */
    private $where = [];

    /**
     * @var int
     */
    private $limitNumber;

    /**
     * @var int
     */
    private $limitOffset = 0;

    /**
     * @var string
     */
    private $orderBy;

    /**
     * @var array
     */
    private $join = [];

    /**
     * @var int
     */
    private $fetchMode = \PDO::FETCH_ASSOC;

    /**
     * @var int
     */
    private $returnType = self::RETURN_TYPE_ENTITY;

    /**
     * @inheritDoc
     */
    public function setEntityClass(string $entityClass): QueryBuilderInterface
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParameters(array $parameters): QueryBuilderInterface
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function from(string $tableName, $alias = null): QueryBuilderInterface
    {
        $this->from = "`$tableName` AS `$alias`";
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function where(string $where): QueryBuilderInterface
    {
        $this->where = [$where];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andWhere(string $where): QueryBuilderInterface
    {
        $this->where[] = $where;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function limit(int $number, int $offset): QueryBuilderInterface
    {
        $this->limitNumber = $number < 1 ? 1 : $number;
        $this->limitOffset = $offset < 0 ? 0 : $offset;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orderBy(string $orderBy): QueryBuilderInterface
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function join(string $join): QueryBuilderInterface
    {
        $this->join[] = $join;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFetchMode(int $fetchMode): QueryBuilderInterface
    {
        $this->fetchMode = $fetchMode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setReturnType(int $returnType = self::RETURN_TYPE_ENTITY): QueryBuilderInterface
    {
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchOne()
    {
        // TODO implements
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(): array
    {
        // TODO implements
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function countResults(): int
    {
        // TODO implements
        return $this;
    }

}