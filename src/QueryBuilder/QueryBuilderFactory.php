<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\Factory;
use Anytime\ORM\EntityManager\FilterCollection;

class QueryBuilderFactory
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    protected $snakeToCamelCaseStringConverter;

    /**
     * @var FilterCollection
     */
    protected $filterCollection;

    /**
     * @var string
     */
    protected $databaseType;

    /**
     * @var string
     */
    protected $entityManagerNamespace;

    /**
     * @var string
     */
    protected $queryBuilderProxyNamespace;

    /**
     * QueryBuilderAbstract constructor.
     * @param Connection $connection
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param string $databaseType
     */
    public function __construct(Connection $connection, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, FilterCollection $filterCollection, string $databaseType, string $entityManagerNamespace, string $queryBuilderProxyNamespace)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->connection = $connection;
        $this->filterCollection = $filterCollection;
        $this->databaseType = $databaseType;
        $this->entityManagerNamespace = $entityManagerNamespace;
        $this->queryBuilderProxyNamespace = $queryBuilderProxyNamespace;
    }

    /**
     * @param string $databaseType
     * @return string
     */
    public static function getQueryBuilderClassByDatabaseType(string $databaseType)
    {
        switch($databaseType) {
            case  Factory::DATABASE_TYPE_MYSQL:     return __NAMESPACE__ . '\\MySqlQueryBuilder';
            default:                                throw new \InvalidArgumentException($databaseType . ' is not a supported database type');
        }
    }

    /**
     * @return QueryBuilderInterface
     */
    public function create(): QueryBuilderInterface
    {
        $qbClass = self::getQueryBuilderClassByDatabaseType($this->databaseType);
        return new $qbClass($this->connection, $this->snakeToCamelCaseStringConverter, $this->filterCollection);
    }

    /**
     * @param QueryBuilderAbstract $queryBuilder
     * @param string $tableName
     * @return QueryBuilderProxyInterface
     */
    public function createProxy(QueryBuilderAbstract $queryBuilder, string $tableName): QueryBuilderProxyInterface
    {
        $queryType = $queryBuilder->getQueryType();

        switch($queryType) {
            case QueryBuilderAbstract::QUERY_TYPE_UPDATE :
                $suffix = 'QueryBuilderUpdateProxy';
                break;
            default:
                throw new \InvalidArgumentException('Query type "'.$queryType.'" cannot be used to create query builders proxies');
        }

        $queryBuilderProxyClass = "\\" . $this->queryBuilderProxyNamespace . '\\' . $this->snakeToCamelCaseStringConverter->convert($tableName) . $suffix;

        if(class_exists($queryBuilderProxyClass)) {
            return new $queryBuilderProxyClass($queryBuilder);
        } else {
            throw new \RuntimeException("Class \"$queryBuilderProxyClass\" not found");
        }
    }
}
