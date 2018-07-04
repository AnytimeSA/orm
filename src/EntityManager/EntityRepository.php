<?php

namespace DVE\EntityORM\EntityManager;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;
use DVE\EntityORM\QueryBuilder\MySqlQueryBuilder;
use DVE\EntityORM\QueryBuilder\QueryBuilderFactory;
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
     * @var QueryBuilderFactory
     */
    protected $queryBuilderFactory;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    protected $snakeToCamelCaseStringConverter;

    /**
     * EntityRepository constructor.
     * @param \PDO $pdo
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param QueryBuilderFactory $queryBuilderFactory
     */
    public function __construct(\PDO $pdo, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, QueryBuilderFactory $queryBuilderFactory)
    {
        $this->pdo = $pdo;
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->queryBuilderFactory = $queryBuilderFactory;
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
        $queryBuilder = new MySqlQueryBuilder($this->pdo, $this->snakeToCamelCaseStringConverter); // TODO Remplacer par une factory
        $queryBuilder
            ->setEntityClass($this->className)
            ->from($this->getTableName(), $alias)
            ->select(($alias ? $alias : $this->getTableName()) . '.*')
        ;
        return $queryBuilder;
    }
}