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
     * QueryBuilderAbstract constructor.
     * @param Connection $connection
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param string $databaseType
     */
    public function __construct(Connection $connection, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, FilterCollection $filterCollection, string $databaseType)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->connection = $connection;
        $this->filterCollection = $filterCollection;
        $this->databaseType = $databaseType;
    }

    /**
     * @return QueryBuilderInterface
     */
    public function create(): QueryBuilderInterface
    {
        switch($this->databaseType) {
            case Factory::DATABASE_TYPE_MYSQL:
                return new MySqlQueryBuilder(
                    $this->connection,
                    $this->snakeToCamelCaseStringConverter,
                    $this->filterCollection
                );
            default:
                throw new \InvalidArgumentException($this->databaseType . 'is not a supported database type');
        }
    }
}
