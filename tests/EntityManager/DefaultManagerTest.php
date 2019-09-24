<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\DefaultEntityRepository;
use Anytime\ORM\EntityManager\DefaultManager;
use Anytime\ORM\EntityManager\EntityManager;
use Anytime\ORM\EntityManager\EntityRepository;
use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderInterface;
use Anytime\ORM\QueryBuilder\SelectQuery;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\Entity\Foo;
use Anytime\ORM\Tests\Stub\Generated\Entity\FooComposite;

class DefaultManagerTest extends ORMTestCase
{
    /**
     * @group Manager
     */
    public function testGetters()
    {
        $manager = $this->getManager(QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $this->assertInstanceOf(EntityRepository::class, $manager->getRepository());
        $this->assertInstanceOf(EntityRepository::class, $manager->getRepository());
    }

    /**
     * @group Manager
     */
    public function testCreateQueryBuilder()
    {
        $manager = $this->getManager(QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $queryBuilder = $manager->createQueryBuilder('f', QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $this->assertInstanceOf(QueryBuilderInterface::class, $queryBuilder);
    }

    /**
     * @group Manager
     */
    public function testCreateDeleteQueryBuilder()
    {
        $manager = $this->getManager(QueryBuilderAbstract::QUERY_TYPE_DELETE);
        $queryBuilder = $manager->createDeleteQueryBuilder('f');
        $this->assertInstanceOf(QueryBuilderInterface::class, $queryBuilder);

    }

    /**
     * @group Manager
     */
    public function testCreateUpdateQueryBuilder()
    {
        $manager = $this->getManager(QueryBuilderAbstract::QUERY_TYPE_UPDATE);
        $queryBuilder = $manager->createUpdateQueryBuilder('f');
        $this->assertInstanceOf(QueryBuilderInterface::class, $queryBuilder);
    }

    /**
     * @group Manager
     */
    public function testSelectQueryReturnsSelectQueryObject()
    {
        $manager = $this->getManager(QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $selectQuery = $manager->selectQuery("SELECT 1;");
        $this->assertInstanceOf(SelectQuery::class, $selectQuery);
    }

    /**
     * @group Manager
     */
    public function testFindByPrimaryKey()
    {
        $manager = $this->getManager(QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $this->assertSame(['bar' => 'baz'], $manager->findByPrimaryKey(1));
    }

    /**
     * @group Manager
     */
    public function testFindAll()
    {
        $manager = $this->getManager(QueryBuilderAbstract::QUERY_TYPE_SELECT);
        $results = $manager->findAll();
        $this->assertCount(2, $results);
        foreach($results as $foo) {
            $this->assertInstanceOf(Foo::class, $foo);
            $this->assertSame(1, $foo->getId());
            $this->assertSame('abc', $foo->getSomeField());
        }
    }

    /**
     * @param $queryType
     * @return DefaultManager
     */
    private function getManager($queryType)
    {
        // Connection mock
        $connectionMockBuilder = $this->prophesize(Connection::class);
        $connectionMockBuilder->prepare('SELECT 1;')->willReturn(new \PDOStatement('SELECT 1;'));
        $connection = $connectionMockBuilder->reveal();

        // SelectQuery mock
        $selectQueryMockBuilder = $this->prophesize(SelectQuery::class);
        $selectQueryMockBuilder->fetchOne()->willReturn(['bar' => 'baz']);
        $selectQueryMockBuilder->fetchAll()->willReturn([
            (new Foo())->setSomeField('abc')->setId(1),
            (new Foo())->setSomeField('abc')->setId(1)
        ]);
        $selectQueryMock = $selectQueryMockBuilder->reveal();

        // QueryBuilder mock
        $queryBuilderAbstractMockBuilder = $this->prophesize(QueryBuilderAbstract::class);
        $queryBuilderAbstractMockBuilder->getFindByPrimaryKeySQLWhere()->willReturn('SELECT 1;');
        $queryBuilderAbstractMockBuilder->getFindByPrimaryKeySQLWhere(['id', 'id2'])->willReturn('SELECT 1;');
        $queryBuilderAbstractMockBuilder->where("SELECT 1;")->willReturn($queryBuilderAbstractMockBuilder->reveal());
        $queryBuilderAbstractMockBuilder->setParameters([1])->willReturn($queryBuilderAbstractMockBuilder->reveal());
        $queryBuilderAbstractMockBuilder->getSelectQuery()->willReturn($selectQueryMock);
        $queryBuilderAbstractMock = $queryBuilderAbstractMockBuilder->reveal();


        // EntityRepository Mock
        $entityRepositoryMockBuilder = $this->prophesize(DefaultEntityRepository::class);
        $entityRepositoryMockBuilder->getClassName()->willReturn(FooComposite::class);

        if($queryType == QueryBuilderAbstract::QUERY_TYPE_SELECT) {
            $entityRepositoryMockBuilder->createQueryBuilder('f', QueryBuilderAbstract::QUERY_TYPE_SELECT)->willReturn($queryBuilderAbstractMock);
            $entityRepositoryMockBuilder->createQueryBuilder(null, 'SELECT')                              ->willReturn($queryBuilderAbstractMock);
        } elseif($queryType == QueryBuilderAbstract::QUERY_TYPE_DELETE) {
            $entityRepositoryMockBuilder->createDeleteQueryBuilder('f')                                   ->willReturn($queryBuilderAbstractMock);
        } elseif($queryType == QueryBuilderAbstract::QUERY_TYPE_UPDATE) {
            $entityRepositoryMockBuilder->createUpdateQueryBuilder('f')                                   ->willReturn($queryBuilderAbstractMock);
        }
        $entityRepository = $entityRepositoryMockBuilder->reveal();

        // EntityManager mock
        $entityManager = $this->prophesize(EntityManager::class)->reveal();

        // FilterCollection mock
        $filterCollection = $this->prophesize(FilterCollection::class)->reveal();

        return new DefaultManager($connection, $entityRepository, $entityManager, $filterCollection);
    }
}
