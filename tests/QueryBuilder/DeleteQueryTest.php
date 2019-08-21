<?php

namespace Anytime\ORM\Tests\QueryBuilder;

use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\DeleteQuery;
use Anytime\ORM\Tests\ORMTestCase;
use Prophecy\Argument;

class DeleteQueryTest extends ORMTestCase
{
    /**
     * @group QueryBuilder
     * @group DeleteQuery
     */
    public function testThatASuccessfulQueryReturnThePDORowCountValue()
    {
        $query = $this->getDeleteQuery();
        $this->assertSame(10, $query->execute());
    }

    /**
     * @group QueryBuilder
     * @group DeleteQuery
     */
    public function testIfPDOReturnAKnownErrorWeHaveAnException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1000);
        $this->expectExceptionMessage('Some PDO error');
        $query = $this->getDeleteQuery($this->getPDOStatementMockWithKnownError(100, 1000, 'Some PDO error'));
        $query->execute();

    }

    /**
     * @group QueryBuilder
     * @group DeleteQuery
     */
    public function testIfPDOReturnAnUnknownErrorWeHaveAnException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1000);
        $this->expectExceptionMessage('Unknown error');
        $query = $this->getDeleteQuery($this->getPDOStatementMockWithKnownError(100, 1000));
        $query->execute();
    }

    /**
     * @group QueryBuilder
     * @group DeleteQuery
     */
    private function getDeleteQuery($stmt = null)
    {
        if(!$stmt) {
            $pdoStatementMockBuilder = $this->prophesize(\PDOStatement::class);
            $pdoStatementMockBuilder->rowCount(Argument::any())->willReturn(10);
            $pdoStatementMockBuilder->execute(Argument::any())->willReturn(true);
            $pdoStatementMockBuilder->errorInfo(Argument::any())->willReturn([]);
            $stmt = $pdoStatementMockBuilder->reveal();
        }

        return new DeleteQuery($this->getConnection(true), $stmt, new FilterCollection(), []);
    }
}
