<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultManager\FooManager;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooEntityRepository;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicManagers;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicRepositories;

class ManagersTest extends ORMTestCase
{
    /**
     * @group Managers
     */
    public function testLoadAndGetManager()
    {
        $managers = $this->getManagers();
        $fooManager = $managers->getFooManager();
        $this->assertInstanceOf(FooManager::class, $fooManager);
        $this->assertInstanceOf(FooEntityRepository::class, $fooManager->getRepository());
    }

    /**
     * @return DynamicManagers
     */
    private function getManagers()
    {
        $dynamicEntityManager = $this->getDynamicEntityManager();
        $queryBuilderFactory = $this->prophesize(QueryBuilderFactory::class)->reveal();

        $managers = new DynamicManagers($dynamicEntityManager->getConnection(), new DynamicRepositories(
            $dynamicEntityManager->getConnection(),
            new SnakeToCamelCaseStringConverter(),
            $queryBuilderFactory
        ));
        $managers->setDynamicEntityManager($dynamicEntityManager);

        return $managers;
    }
}
