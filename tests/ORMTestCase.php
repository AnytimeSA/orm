<?php

namespace Anytime\ORM\Tests;

use Anytime\ORM\EntityManager\Connection;
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
}
