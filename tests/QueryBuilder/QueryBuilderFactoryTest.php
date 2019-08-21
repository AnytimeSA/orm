<?php

namespace Anytime\ORM\Tests\QueryBuilder;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Factory;
use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\MySqlQueryBuilder;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\QueryBuilder\QueryBuilderInterface;
use Anytime\ORM\QueryBuilder\QueryBuilderProxyInterface;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\QueryBuilderProxy\FooQueryBuilderUpdateProxy;

class QueryBuilderFactoryTest extends ORMTestCase
{
    /**
     * @group QueryBuilder
     * @group QueryBuilderFactory
     */
    public function testGetQueryBuilderClassByDatabaseType()
    {
        $this->assertSame(MySqlQueryBuilder::class, QueryBuilderFactory::getQueryBuilderClassByDatabaseType(Factory::DATABASE_TYPE_MYSQL));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('UnknownDatabaseType is not a supported database type');
        QueryBuilderFactory::getQueryBuilderClassByDatabaseType('UnknownDatabaseType');
    }

    /**
     * @group QueryBuilder
     * @group QueryBuilderFactory
     */
    public function testCreateMethodSucceed()
    {
        $factory = $this->getQueryBuilderFactory();
        $this->assertInstanceOf(QueryBuilderInterface::class, $factory->create());
    }

    /**
     * @group QueryBuilder
     * @group QueryBuilderFactory
     */
    public function testCreateProxyMethodSucceed()
    {
        $factory = $this->getQueryBuilderFactory();

        /** @var QueryBuilderAbstract $qb */
        $qb = $factory->create();
        $qb->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE);
        $proxy = $factory->createProxy($qb, 'foo');
        $this->assertInstanceOf(QueryBuilderProxyInterface::class, $proxy);
        $this->assertInstanceOf(FooQueryBuilderUpdateProxy::class, $factory->createProxy($qb, 'foo'));
    }


    /**
     * @return QueryBuilderFactory
     */
    private function getQueryBuilderFactory()
    {
        return new QueryBuilderFactory(
            $this->getConnection(true),
            new SnakeToCamelCaseStringConverter(),
            new FilterCollection(),
            Factory::DATABASE_TYPE_MYSQL,
            'Anytime\ORM\Tests\Stub\Generated\EntityManager',
            'Anytime\ORM\Tests\Stub\Generated\QueryBuilderProxy'
        );
    }
}
