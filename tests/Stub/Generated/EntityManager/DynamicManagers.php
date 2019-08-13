<?php

namespace Anytime\ORM\Tests\Stub\Generated\EntityManager;

use Anytime\ORM\EntityManager\Managers;
use Anytime\ORM\EntityManager\Connection;

class DynamicManagers extends Managers
{
    private $dynamicRepositories;
    private $entityManager;
    public function __construct(Connection $connection, DynamicRepositories $dynamicRepositories) {
        $this->dynamicRepositories = $dynamicRepositories;
        parent::__construct($connection);
    }
    /**
     * @return FooManager|\Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultManager\FooManager
     */
    public function getFooManager(): \Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultManager\FooManager
    {
        return $this->loadAndGetManager(
            'Anytime\ORM\Tests\Stub\User\EntityManager\DefaultManager\FooManager',
            'Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultManager\FooManager',
            $this->dynamicRepositories->getFooEntityRepository(),
            $this->entityManager
        );
    }

    /**
     * @param DynamicEntityManager $dynamicEntityManager
     */
    public function setDynamicEntityManager(DynamicEntityManager $dynamicEntityManager)
    {
        $this->entityManager = $dynamicEntityManager;
    }
    /**
     * @return DynamicEntityManager     */
    public function getDynamicEntityManager(): DynamicEntityManager
    {
        return $this->entityManager;
    }
}
