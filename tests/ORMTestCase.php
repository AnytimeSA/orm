<?php

namespace Anytime\ORM\Tests;

use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\Factory;
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
            ->setUserEntityRepositoryNamespace('Anytime\ORM\Tests\Stub\User\EntityManager')
            ->setQueryBuilderProxyNamespace('Anytime\ORM\Tests\Stub\Generated\QueryBuilderProxyAnytime')
            ->setUserManagerNamespace('Anytime\ORM\Tests\Stub\Generated\EntityManager')
            ->setEntityDirectory('../Stub/Generated/Entity')
            ->setEntityManagerDirectory('../Stub/Generated/EntityManager/')
            ->setUserEntityRepositoryDirectory('../Stub/User/EntityManager/EntityRepository')
            ->setUserManagerDirectory('../Stub/Generated/EntityManager/Manager')
            ->setQueryBuilderProxyDirectory('../Stub/Generated/EntityManager/QueryBuilderProxy')
            ;
    }
}
