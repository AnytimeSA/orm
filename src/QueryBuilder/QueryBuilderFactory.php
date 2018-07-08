<?php

namespace DVE\EntityORM\QueryBuilder;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;
use DVE\EntityORM\EntityManager\Factory;

class QueryBuilderFactory
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    protected $snakeToCamelCaseStringConverter;

    /**
     * QueryBuilderAbstract constructor.
     * @param \PDO $pdo
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     */
    public function __construct(\PDO $pdo, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->pdo = $pdo;
    }

    /**
     * @param string $databaseType
     * @return QueryBuilderInterface
     */
    public function create(string $databaseType): QueryBuilderInterface
    {
        switch($databaseType) {
            case Factory::DATABASE_TYPE_MYSQL:
                return new MySqlQueryBuilder($this->pdo, $this->snakeToCamelCaseStringConverter);
            default:
                throw new \InvalidArgumentException($databaseType . 'is not a supported database type');
        }
    }
}
