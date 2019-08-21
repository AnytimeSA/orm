<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooEntityRepository;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicRepositories;

class RepositoriesTest extends ORMTestCase
{
    /**
     * @group Managers
     */
    public function testLoadAndGetRepositoryManager()
    {
        $repositories = $this->getRepositories();
        $fooRepository = $repositories->getFooEntityRepository();
        $this->assertInstanceOf(FooEntityRepository::class, $fooRepository);
    }

    /**
     * @return DynamicRepositories
     */
    private function getRepositories()
    {
        $dynamicEntityManager = $this->getDynamicEntityManager();
        $queryBuilderFactory = $this->prophesize(QueryBuilderFactory::class)->reveal();
        $repositories = new DynamicRepositories($dynamicEntityManager->getConnection(), new SnakeToCamelCaseStringConverter(), $queryBuilderFactory);
        return $repositories;
    }
}
