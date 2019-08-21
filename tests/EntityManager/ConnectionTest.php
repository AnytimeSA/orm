<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\Tests\ORMTestCase;

class ConnectionTest extends ORMTestCase
{
    /**
     * @group Connection
     */
    public function testConnectionGetPDOOrNull()
    {
        $connection = $this->getConnection(true);
        $this->assertInstanceOf(\PDO::class, $connection->getPDO());

        $connection = $this->getConnection(false);
        $this->assertNull($connection->getPDO());
    }

    /**
     * @group Connection
     */
    public function testConnectionSetPDO()
    {
        $connection = $this->getConnection(false);
        $pdoMock = $this->prophesize(\PDO::class)->reveal();
        $this->assertInstanceOf(Connection::class, $connection->setPDO($pdoMock));
        $this->assertInstanceOf(\PDO::class, $connection->getPDO());
    }

    /**
     * @group Connection
     */
    public function testCloseConnection()
    {
        $connection = $this->getConnection(true);
        $this->assertTrue($connection->closeConnection(), 'When a PDOobject is set in the connection object, the closeConnection method should returns true.');
        $this->assertNull($connection->getPDO(), 'When the closeConnection() method is called the pdo property is set to null in the Connection object.');
        $this->assertFalse($connection->closeConnection(), 'When the closeConnection() method is called and the pdo property is already set to null, it should returns false.');
    }

    /**
     * @group Connection
     */
    public function testPDONativesMethodsAreCalledWhenCallingItWithTheConnectionObject()
    {
        $pdoStatementMock = $this->prophesize(\PDOStatement::class)->reveal();

        $pdoMockBuilder = $this->getPdoMockBuilder();
        $pdoMockBuilder->beginTransaction()->willReturn(true)->shouldBeCalled();
        $pdoMockBuilder->commit()->willReturn(true)->shouldBeCalled();
        $pdoMockBuilder->errorCode()->willReturn('Code ABC')->shouldBeCalled();
        $pdoMockBuilder->errorInfo()->willReturn(['info' => 'ABC'])->shouldBeCalled();
        $pdoMockBuilder->exec($pdoStatementMock)->willReturn(1)->shouldBeCalled();
        $pdoMockBuilder->getAttribute('attr_name')->willReturn('ABC')->shouldBeCalled();
        $pdoMockBuilder->inTransaction()->willReturn(true)->shouldBeCalled();
        $pdoMockBuilder->lastInsertId(null)->willReturn(123)->shouldBeCalled();
        $pdoMockBuilder->prepare($pdoStatementMock, [])->willReturn($pdoStatementMock)->shouldBeCalled();
        $pdoMockBuilder->query($pdoStatementMock, \PDO::ATTR_DEFAULT_FETCH_MODE, null, [])->willReturn($pdoStatementMock)->shouldBeCalled();
        $pdoMockBuilder->quote("'", \PDO::PARAM_STR)->willReturn("'")->shouldBeCalled();
        $pdoMockBuilder->rollBack()->willReturn(true)->shouldBeCalled();
        $pdoMockBuilder->setAttribute('attr_name', 1)->willReturn(true)->shouldBeCalled();

        $pdoMock = $pdoMockBuilder->reveal();

        $connection = $this->getConnection(true, $pdoMock);
        $this->assertTrue($connection->beginTransaction());

        $connection->commit();
        $connection->errorCode();
        $connection->errorInfo();
        $connection->exec($pdoStatementMock);
        $connection->getAttribute('attr_name');
        $connection->inTransaction();
        $connection->lastInsertId();
        $connection->prepare($pdoStatementMock);
        $connection->query($pdoStatementMock);
        $connection->quote("'");
        $connection->rollBack();
        $connection->setAttribute('attr_name', 1);

        $this->assertSame(\PDO::getAvailableDrivers(), Connection::getAvailableDrivers());
    }
}
