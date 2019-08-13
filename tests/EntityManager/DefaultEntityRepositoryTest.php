<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\DefaultEntityRepository;
use Anytime\ORM\QueryBuilder\MySqlQueryBuilder;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\QueryBuilder\QueryBuilderInterface;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\Entity\Foo;

class DefaultEntityRepositoryTest extends ORMTestCase
{
    /**
     * @group EntityRepository
     */
    public function testSettersAndGetters()
    {
        $entityRepository = $this->getDefaultEntityRepository(QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $this->assertNull($entityRepository->getTableName());
        $this->assertNull($entityRepository->getClassName());
        $this->assertSame($entityRepository, $entityRepository->setTableName(Foo::TABLENAME));
        $this->assertSame($entityRepository, $entityRepository->setClassName(Foo::class));
        $this->assertSame(Foo::TABLENAME, $entityRepository->getTableName());
        $this->assertSame(Foo::class, $entityRepository->getClassName());
    }

    /**
     * @group EntityRepository
     */
    public function testCreateQueryBuilders()
    {
        // SELECT
        $entityRepository = $this->getDefaultEntityRepository(QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $entityRepository->setTableName(Foo::TABLENAME)->setClassName(Foo::class);
        $queryBuilder = $entityRepository->createQueryBuilder('f', QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $this->assertInstanceOf(QueryBuilderInterface::class, $queryBuilder);

        // DELETE
        $entityRepository = $this->getDefaultEntityRepository(QueryBuilderAbstract::QUERY_TYPE_DELETE);
        $entityRepository->setTableName(Foo::TABLENAME)->setClassName(Foo::class);
        $queryBuilder = $entityRepository->createDeleteQueryBuilder('f', QueryBuilderAbstract::QUERY_TYPE_DELETE);
        $this->assertInstanceOf(QueryBuilderInterface::class, $queryBuilder);

        // UPDATE
        $entityRepository = $this->getDefaultEntityRepository(QueryBuilderAbstract::QUERY_TYPE_UPDATE);
        $entityRepository->setTableName(Foo::TABLENAME)->setClassName(Foo::class);
        $queryBuilder = $entityRepository->createUpdateQueryBuilder('f', QueryBuilderAbstract::QUERY_TYPE_UPDATE);
        $this->assertInstanceOf(QueryBuilderInterface::class, $queryBuilder);
    }



    /**
     * @return DefaultEntityRepository
     */
    private function getDefaultEntityRepository(string $queryType)
    {
        // As we can ask to return same object instance in the mock (object chaining) we have to fake it with multiple object :/
        $queryBuilderMock5 = $this->prophesize(MySqlQueryBuilder::class)->reveal();

        $queryBuilderMock4Builder = $this->prophesize(MySqlQueryBuilder::class);
        $queryBuilderMock4Builder->select($queryType == QueryBuilderAbstract::QUERY_TYPE_SELECT ? 'f.*' : 'foo_entity.*')->willReturn($queryBuilderMock5);
        $queryBuilderMock4 = $queryBuilderMock4Builder->reveal();

        $queryBuilderMock3Builder = $this->prophesize(MySqlQueryBuilder::class);
        $queryBuilderMock3Builder->from(Foo::TABLENAME, ($queryType == QueryBuilderAbstract::QUERY_TYPE_SELECT ? 'f' : null))->willReturn($queryBuilderMock4);
        $queryBuilderMock3 = $queryBuilderMock3Builder->reveal();

        $queryBuilderMock2Builder = $this->prophesize(MySqlQueryBuilder::class);
        $queryBuilderMock2Builder->setEntityClass(Foo::class)->willReturn($queryBuilderMock3);
        $queryBuilderMock2 = $queryBuilderMock2Builder->reveal();

        $queryBuilderMockBuilder = $this->prophesize(MySqlQueryBuilder::class);
        $queryBuilderMockBuilder->setQueryType($queryType)->willReturn($queryBuilderMock2);
        $queryBuilderMock = $queryBuilderMockBuilder->reveal();

        $queryBuilderFactoryMockBuilder = $this->prophesize(QueryBuilderFactory::class);
        $queryBuilderFactoryMockBuilder->create()->willReturn($queryBuilderMock);

        $queryBuilderFactoryMock = $queryBuilderFactoryMockBuilder->reveal();

        return new DefaultEntityRepository(
            $this->getConnection(true),
            $this->prophesize(SnakeToCamelCaseStringConverter::class)->reveal(),
            $queryBuilderFactoryMock
        );
    }
}
