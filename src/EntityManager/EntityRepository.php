<?php

namespace Anytime\ORM\EntityManager;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\QueryBuilder\QueryBuilderInterface;

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
     * @var Connection
     */
    protected $connection;

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
     * @param Connection $connection
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param QueryBuilderFactory $queryBuilderFactory
     */
    public function __construct(Connection $connection , SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, QueryBuilderFactory $queryBuilderFactory)
    {
        $this->connection = $connection;
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
     * @param string $queryType
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder($alias = null, string $queryType = QueryBuilderAbstract::QUERY_TYPE_SELECT): QueryBuilderInterface
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->setQueryType($queryType)
            ->setEntityClass($this->className)
            ->from($this->getTableName(), $alias)
            ->select(($alias ? $alias : $this->getTableName()) . '.*')
        ;
        return $queryBuilder;
    }

    /**
     * @param string|null $alias
     * @return QueryBuilderInterface
     */
    public function createDeleteQueryBuilder($alias = null)
    {
        return $this->createQueryBuilder($alias, QueryBuilderAbstract::QUERY_TYPE_DELETE);
    }

    /**
     * @param string|null $alias
     * @return QueryBuilderInterface
     */
    public function createUpdateQueryBuilder($alias = null)
    {
        return $this->createQueryBuilder($alias, QueryBuilderAbstract::QUERY_TYPE_UPDATE);
    }
}
