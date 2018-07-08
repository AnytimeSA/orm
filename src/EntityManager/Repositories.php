<?php

namespace DVE\EntityORM\EntityManager;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;
use DVE\EntityORM\QueryBuilder\QueryBuilderFactory;

class Repositories
{
    /**
     * @var EntityRepository[]
     */
    protected $loadedRepositories = [];

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    protected $snakeToCamelCaseStringConverter;

    /**
     * @var QueryBuilderFactory
     */
    protected $queryBuilderFactory;

    /**
     * EntityManager constructor.
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
     * @param string $class
     * @param string $tableName
     * @param string $entityClassName
     * @return EntityRepository
     */
    protected function loadAndGetRepository(string $class, string $tableName, string $entityClassName)
    {
        if(array_key_exists($class, $this->loadedRepositories)) {
            return $this->loadedRepositories[$class];
        }

        if(class_exists($class)) {
            return (new $class($this->pdo, $this->snakeToCamelCaseStringConverter, $this->queryBuilderFactory))
                ->setTableName($tableName)
                ->setClassName($entityClassName);
        } else {
            return (new DefaultEntityRepository($this->pdo, $this->snakeToCamelCaseStringConverter, $this->queryBuilderFactory))
                ->setTableName($tableName)
                ->setClassName($entityClassName);
            ;
        }
    }
}
