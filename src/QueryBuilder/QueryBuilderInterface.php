<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\EntityManager\Entity;

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
     * @param string $select
     * @return QueryBuilderInterface
     */
    public function select(string $select): QueryBuilderInterface;

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
     * @param string $groupBy
     * @return QueryBuilderInterface
     */
    public function groupBy(string $groupBy): QueryBuilderInterface;

    /**
     * @param string $join
     * @return QueryBuilderInterface
     */
    public function join(string $join): QueryBuilderInterface;

    /**
     * @return string
     */
    public function getSelectSQL(): string;

    /**
     * @return string
     */
    public function getDeleteByPrimaryKeySQL(): string;

    /**
     * @return string
     */
    public function getDeleteByCriteriaSQL(): string;

    /**
     * @param array $fields
     * @return string
     */
    public function getInsertSQL(array $fields): string;

    /**
     * @param array $fields
     * @return string
     */
    public function getUpdateByPrimaryKeySQL(array $fields): string;

    /**
     * @param array $fields
     * @return string
     */
    public function getUpdateByCriteriaSQL(array $fields): string;

    /**
     * @return QueryAbstract|SelectQueryInterface
     */
    public function getSelectQuery(): QueryAbstract;

    /**
     * @param Entity $entity
     * @return QueryAbstract|InsertQueryInterface
     */
    public function getInsertQuery(Entity $entity): QueryAbstract;

    /**
     * @param Entity|null $entity
     * @return QueryAbstract|UpdateQueryInterface
     */
    public function getUpdateQuery(Entity $entity = null): QueryAbstract;

    /**
     * @param Entity|null $entity
     * @return QueryAbstract|DeleteQueryInterface
     */
    public function getDeleteQuery(Entity $entity = null): QueryAbstract;

    /**
     * @param array $primaryKeys
     * @return string
     */
    public function getFindByPrimaryKeySQLWhere(array $primaryKeys): string;
}
