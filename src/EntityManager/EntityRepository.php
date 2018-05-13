<?php

namespace DVE\EntityORM\EntityManager;

use DVE\EntityORM\QueryBuilder\MySqlQueryBuilder;
use DVE\EntityORM\QueryBuilder\QueryBuilderInterface;

abstract class EntityRepository
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * EntityRepository constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return EntityRepository
     */
    public function setTableName(string $tableName): EntityRepository
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return EntityRepository
     */
    public function setClassName(string $className): EntityRepository
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @param string|null $alias
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder($alias = null): QueryBuilderInterface
    {
        $queryBuilder = new MySqlQueryBuilder($this->pdo); // TODO Remplacer par une factory
        $queryBuilder
            ->setEntityClass($this->className)
            ->from($this->getTableName(), $alias)
        ;
        return $queryBuilder;
    }
}