<?php

namespace DVE\EntityORM\EntityManager;

abstract class EntityManager
{
    /**
     * @var EntityRepository[]
     */
    protected $loadedRepositories;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * EntityManager constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $class
     * @param string $tableName
     * @return EntityRepository
     */
    protected function loadAndGetRepository(string $class, string $tableName)
    {
        if(array_key_exists($class, $this->loadedRepositories)) {
            return $this->loadedRepositories[$class];
        }

        if(class_exists($class)) {
            return (new $class())->setTableName($tableName);
        } else {
            return (new DefaultEntityRepository())->setTableName($tableName);
        }
    }
}