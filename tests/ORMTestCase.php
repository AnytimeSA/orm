<?php

namespace Anytime\ORM\Tests;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\Factory;
use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicEntityManager;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicManagers;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicRepositories;
use PHPUnit\Framework\TestCase;

class ORMTestCase extends TestCase
{
    /**
     * @param bool $getPdo
     * @param \PDO|null $pdo
     * @return Connection
     */
    protected function getConnection(bool $getPdo, \PDO $pdo = null)
    {
        if($getPdo) {
            $connection = new Connection($pdo ? $pdo : $this->getPdoMockBuilder()->reveal());
        } else {
            $connection = new Connection();
        }
        return $connection;
    }

    /**
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function getPdoMockBuilder()
    {
        $pdoMock = $this->prophesize(\PDO::class);
        return $pdoMock;
    }

    /**
     * @return Factory
     */
    protected function getEntityManagerFactory()
    {
        return (new Factory())
            ->setDatabaseType(Factory::DATABASE_TYPE_MYSQL)
            ->setEntityNamespace('Anytime\ORM\Tests\Stub\Generated\Entity')
            ->setEntityManagerNamespace('Anytime\ORM\Tests\Stub\Generated\EntityManager')
            ->setUserEntityRepositoryNamespace('Anytime\ORM\Tests\Stub\User\EntityRepository')
            ->setQueryBuilderProxyNamespace('Anytime\ORM\Tests\Stub\Generated\QueryBuilderProxyAnytime')
            ->setUserManagerNamespace('Anytime\ORM\Tests\Stub\User\EntityManager')
            ->setEntityDirectory('../Stub/Generated/Entity')
            ->setEntityManagerDirectory('../Stub/Generated/EntityManager/')
            ->setUserEntityRepositoryDirectory('../Stub/User/EntityRepository')
            ->setUserManagerDirectory('../Stub/Generated/Manager')
            ->setQueryBuilderProxyDirectory('../Stub/Generated/EntityManager/QueryBuilderProxy')
            ;
    }

    /**
     * @return DynamicEntityManager
     */
    protected function getDynamicEntityManager()
    {
        $connection = $this->getConnection(true);
        $snakeToCamelCaseConverter = new SnakeToCamelCaseStringConverter();
        $queryBuilderFactory = $this->prophesize(QueryBuilderFactory::class)->reveal();

        $dynamicRepositories = new DynamicRepositories(
            $connection,
            $snakeToCamelCaseConverter,
            $queryBuilderFactory
        );

        $em = new DynamicEntityManager(
            $connection,
            $snakeToCamelCaseConverter,
            $dynamicRepositories,
            new DynamicManagers(
                $connection,
                $dynamicRepositories
            ),
            $queryBuilderFactory,
            new FilterCollection(),
            Factory::DATABASE_TYPE_MYSQL
        );

        return $em;
    }
}
