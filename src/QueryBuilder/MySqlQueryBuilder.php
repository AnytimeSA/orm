<?php

namespace DVE\EntityORM\QueryBuilder;

class MySqlQueryBuilder extends QueryBuilderAbstract
{
    /**
     * @inheritDoc
     */
    public function setEntityClass(string $entityClass): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParameters(array $parameters): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function from(string $tableName, $alias = null): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function where(string $where): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andWhere(string $where): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function limit(int $number, int $offset): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orderBy(string $orderBy): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function join(string $join): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFetchMode(int $fetchMode): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setReturnType(int $returnType = self::RETURN_TYPE_ENTITY): QueryBuilderInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchOne()
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(): array
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function countResults(): int
    {
        return $this;
    }

}