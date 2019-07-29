<?php

namespace Anytime\ORM\EntityManager;

class Managers
{
    /**
     * @var Manager[]
     */
    protected $loadedManagers = [];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * EntityManager constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
            return (new $class($this->connection, $entityRepository, $entityManager, $entityManager->getFilterCollection()));
        } elseif(class_exists($defaultClass)) {
            return (new $defaultClass($this->connection, $entityRepository, $entityManager, $entityManager->getFilterCollection()));
        } else {
            return (new DefaultManager($this->connection, $entityRepository, $entityManager, $entityManager->getFilterCollection()));
        }
    }
}
