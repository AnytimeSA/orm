<?php

namespace DVE\EntityORM\QueryBuilder;

class MySqlQueryBuilder extends QueryBuilderAbstract
{
    /**
     * @inheritDoc
     */
    public function setEntityClass(string $entityClass): QueryBuilderInterface
    {
        // TODO: Implement setEntityClass() method.
    }

    /**
     * @inheritDoc
     */
    public function setParameters(array $parameters): QueryBuilderInterface
    {
        // TODO: Implement setParameters() method.
    }

    /**
     * @inheritDoc
     */
    public function from(string $tableName, $alias = null): QueryBuilderInterface
    {
        // TODO: Implement from() method.
    }

    /**
     * @inheritDoc
     */
    public function where(string $where): QueryBuilderInterface
    {
        // TODO: Implement where() method.
    }

    /**
     * @inheritDoc
     */
    public function andWhere(string $where): QueryBuilderInterface
    {
        // TODO: Implement andWhere() method.
    }

    /**
     * @inheritDoc
     */
    public function limit(int $number, int $offset): QueryBuilderInterface
    {
        // TODO: Implement limit() method.
    }

    /**
     * @inheritDoc
     */
    public function orderBy(string $orderBy): QueryBuilderInterface
    {
        // TODO: Implement orderBy() method.
    }

    /**
     * @inheritDoc
     */
    public function join(string $join): QueryBuilderInterface
    {
        // TODO: Implement join() method.
    }

    /**
     * @inheritDoc
     */
    public function setFetchMode(int $fetchMode): QueryBuilderInterface
    {
        // TODO: Implement setFetchMode() method.
    }

    /**
     * @inheritDoc
     */
    public function setReturnType(int $returnType = self::RETURN_TYPE_ENTITY): QueryBuilderInterface
    {
        // TODO: Implement setReturnType() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchOne()
    {
        // TODO: Implement fetchOne() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(): array
    {
        // TODO: Implement fetchAll() method.
    }

    /**
     * @inheritDoc
     */
    public function countResults(): int
    {
        // TODO: Implement countResults() method.
    }

}