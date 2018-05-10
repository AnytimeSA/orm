<?php

namespace DVE\EntityORM\QueryBuilder;

interface QueryBuilderInterface
{
    /**
     * @param string $entityClass
     * @return QueryBuilderInterface
     */
    public function setEntityClass(string $entityClass): QueryBuilderInterface;

    /**
     * @param array $parameters
     * @return QueryBuilderInterface
     */
    public function setParameters(array $parameters): QueryBuilderInterface;

    /**
     * @param string $tableName
     * @param string|null $alias
     * @return QueryBuilderInterface
     */
    public function from(string $tableName, $alias = null): QueryBuilderInterface;

    /**
     * @param string $where
     * @return QueryBuilderInterface
     */
    public function where(string $where): QueryBuilderInterface;

    /**
     * @param string $where
     * @return QueryBuilderInterface
     */
    public function andWhere(string $where): QueryBuilderInterface;

    /**
     * @param int $number
     * @param int $offset
     * @return QueryBuilderInterface
     */
    public function limit(int $number, int $offset): QueryBuilderInterface;

    /**
     * @param string $orderBy
     * @return QueryBuilderInterface
     */
    public function orderBy(string $orderBy): QueryBuilderInterface;

    /**
     * @param string $join
     * @return QueryBuilderInterface
     */
    public function join(string $join): QueryBuilderInterface;

    /**
     * @param int $fetchMode
     * @return QueryBuilderInterface
     */
    public function setFetchMode(int $fetchMode): QueryBuilderInterface;

    /**
     * @param int $returnType
     * @return QueryBuilderInterface
     */
    public function setReturnType(int $returnType = self::RETURN_TYPE_ENTITY): QueryBuilderInterface;

    /**
     * @return mixed
     */
    public function fetchOne();

    /**
     * @return array
     */
    public function fetchAll(): array;

    /**
     * @return int
     */
    public function countResults(): int;
}