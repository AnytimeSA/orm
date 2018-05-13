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
     * @return string
     */
    public function getSQL(): string;

    /**
     * @return Query
     */
    public function getQuery(): QueryInterface;
}