<?php

namespace DVE\EntityORM\EntityManager;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;

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
     * EntityManager constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter)
    {
        $this->pdo = $pdo;
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
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
            return (new $class($this->pdo, $this->snakeToCamelCaseStringConverter))
                ->setTableName($tableName)
                ->setClassName($entityClassName);
        } else {
            return (new DefaultEntityRepository($this->pdo, $this->snakeToCamelCaseStringConverter))
                ->setTableName($tableName)
                ->setClassName($entityClassName);
            ;
        }
    }
}