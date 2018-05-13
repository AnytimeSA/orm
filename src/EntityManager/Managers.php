<?php

namespace DVE\EntityORM\EntityManager;

class Managers
{
    /**
     * @var Manager[]
     */
    protected $loadedManagers = [];

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
     * @param EntityRepository $entityRepository
     * @return Manager
     */
    protected function loadAndGetManager(string $class, EntityRepository $entityRepository)
    {
        if(array_key_exists($class, $this->loadedManagers)) {
            return $this->loadedManagers[$class];
        }

        if(class_exists($class)) {
            return (new $class($this->pdo, $entityRepository));
        } else {
            return (new DefaultManager($this->pdo, $entityRepository));
        }
    }
}