<?php

namespace Anytime\ORM\Tests;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\Factory;
use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\DeleteQuery;
use Anytime\ORM\QueryBuilder\InsertQuery;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\QueryBuilder\UpdateQuery;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicEntityManager;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicManagers;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicRepositories;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

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
        $pdoMockBuilder = $this->prophesize(\PDO::class);
        $pdoMockBuilder->lastInsertId(Argument::any())->willReturn(10);
        $pdoMockBuilder
            ->prepare(Argument::any(), Argument::any())
            ->willReturn(
                $this->prophesize(\PDOStatement::class)->reveal()
            )
        ;
        return $pdoMockBuilder;
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
        //Connection
        $connection = $this->getConnection(true);

        // SnakeToCamelCaseConverter
        $snakeToCamelCaseConverter = new SnakeToCamelCaseStringConverter();

        // InsertQuery
        $insertQueryMockBuilder = $this->prophesize(InsertQuery::class);
        $insertQueryMockBuilder->execute(Argument::any())->willReturn(1);
        $insertQuery = $insertQueryMockBuilder->reveal();

        // UpdateQuery
        $updateQueryMockBuilder = $this->prophesize(UpdateQuery::class);
        $updateQueryMockBuilder->execute(Argument::any())->willReturn(1);
        $updateQuery = $updateQueryMockBuilder->reveal();

        // DeleteQuery
        $deleteQueryMockBuilder = $this->prophesize(DeleteQuery::class);
        $deleteQueryMockBuilder->execute(Argument::any())->willReturn(1);
        $deleteQuery = $deleteQueryMockBuilder->reveal();

        // QueryBuilder
        $queryBuilderMockBuilder = $this->prophesize(QueryBuilderAbstract::class);
        $queryBuilderMockBuilder->setQueryType(Argument::any())->willReturn($queryBuilderMockBuilder->reveal());
        $queryBuilderMockBuilder->getInsertQuery(Argument::any())->willReturn($insertQuery);
        $queryBuilderMockBuilder->getUpdateQuery(Argument::any())->willReturn($updateQuery);
        $queryBuilderMockBuilder->getDeleteQuery(Argument::any())->willReturn($deleteQuery);
        $queryBuilderMockBuilder->setEntityClass(Argument::any())->willReturn($queryBuilderMockBuilder->reveal());

        // QueryBuilderFactory
        $queryBuilderFactoryMockBuilder = $this->prophesize(QueryBuilderFactory::class);
        $queryBuilderFactoryMockBuilder->create(Argument::any())->willReturn($queryBuilderMockBuilder->reveal());
        $queryBuilderFactory = $queryBuilderFactoryMockBuilder->reveal();

        // DynamicRepositories
        $dynamicRepositories = new DynamicRepositories(
            $connection,
            $snakeToCamelCaseConverter,
            $queryBuilderFactory
        );

        // EntityManager
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

    /**
     * @param $errorCode
     * @param $driverErrorCode
     * @param null $driverErrorString
     * @return \PDOStatement
     */
    protected function getPDOStatementMockWithKnownError($errorCode, $driverErrorCode, $driverErrorString = null)
    {
        $errorArray = [
            $errorCode,
            $driverErrorCode
        ];

        if($driverErrorString) {
            $errorArray[] = $driverErrorString;
        }

        $pdoStatementMockBuilder = $this->prophesize(\PDOStatement::class);
        $pdoStatementMockBuilder->execute(Argument::any())->willReturn(true);
        $pdoStatementMockBuilder->errorInfo(Argument::any())->willReturn($errorArray);
        return $pdoStatementMockBuilder->reveal();
    }
}
