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
     * @param EntityManager $entityManager
     * @return Manager
     */
    protected function loadAndGetManager(string $class, string $defaultClass, EntityRepository $entityRepository, EntityManager $entityManager)
    {
        if(array_key_exists($class, $this->loadedManagers)) {
            return $this->loadedManagers[$class];
        }

        if(class_exists($class)) {
            return (new $class($this->pdo, $entityRepository, $entityManager));
        } elseif(class_exists($defaultClass)) {
            return (new $defaultClass($this->pdo, $entityRepository, $entityManager));
        } else {
            return (new DefaultManager($this->pdo, $entityRepository, $entityManager));
        }
    }
}
