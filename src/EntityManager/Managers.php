<?php

namespace Anytime\ORM\EntityManager;

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
     * @param string $defaultClass
     * @param EntityRepository $entityRepository
     * @return Manager
     */
    protected function loadAndGetManager(string $class, string $defaultClass, EntityRepository $entityRepository)
    {
        if(array_key_exists($class, $this->loadedManagers)) {
            return $this->loadedManagers[$class];
        }

        if(class_exists($class)) {
            return (new $class($this->pdo, $entityRepository));
        } elseif(class_exists($defaultClass)) {
            return (new $defaultClass($this->pdo, $entityRepository));
        } else {
            return (new DefaultManager($this->pdo, $entityRepository));
        }
    }
}
