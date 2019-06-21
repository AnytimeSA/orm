<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\Factory;

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
     * @var string
     */
    protected $databaseType;

    /**
     * QueryBuilderAbstract constructor.
     * @param Connection $connection
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param string $databaseType
     */
    public function __construct(Connection $connection, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, string $databaseType)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->connection = $connection;
        $this->databaseType = $databaseType;
    }

    /**
     * @return QueryBuilderInterface
     */
    public function create(): QueryBuilderInterface
    {
        switch($this->databaseType) {
            case Factory::DATABASE_TYPE_MYSQL:
                return new MySqlQueryBuilder($this->connection, $this->snakeToCamelCaseStringConverter);
            default:
                throw new \InvalidArgumentException($this->databaseType . 'is not a supported database type');
        }
    }
}
