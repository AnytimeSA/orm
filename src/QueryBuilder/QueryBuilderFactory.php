<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Factory;

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
     * @var string
     */
    protected $databaseType;

    /**
     * QueryBuilderAbstract constructor.
     * @param \PDO $pdo
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param string $databaseType
     */
    public function __construct(\PDO $pdo, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, string $databaseType)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->pdo = $pdo;
        $this->databaseType = $databaseType;
    }

    /**
     * @return QueryBuilderInterface
     */
    public function create(): QueryBuilderInterface
    {
        switch($this->databaseType) {
            case Factory::DATABASE_TYPE_MYSQL:
                return new MySqlQueryBuilder($this->pdo, $this->snakeToCamelCaseStringConverter);
            default:
                throw new \InvalidArgumentException($this->databaseType . 'is not a supported database type');
        }
    }
}
