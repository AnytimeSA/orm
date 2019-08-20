<?php

namespace Anytime\ORM\Tests\QueryBuilder;

use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\InsertQuery;
use Anytime\ORM\Tests\ORMTestCase;
use Prophecy\Argument;

class InsertQueryTest extends ORMTestCase
{
    /**
     * @group QueryBuilder
     * @group DeleteQuery
     */
    public function testThatASuccessfulQueryReturnThePDORowCountValue()
    {
        $query = $this->getInsertQuery();
        $this->assertSame(10, $query->execute());
    }


    /**
     * @group QueryBuilder
     * @group DeleteQuery
     */
    private function getInsertQuery($stmt = null)
    {
        if(!$stmt) {
            $pdoStatementMockBuilder = $this->prophesize(\PDOStatement::class);
            $pdoStatementMockBuilder->rowCount(Argument::any())->willReturn(10);
            $pdoStatementMockBuilder->execute(Argument::any())->willReturn(true);
            $pdoStatementMockBuilder->errorInfo(Argument::any())->willReturn([]);
            $stmt = $pdoStatementMockBuilder->reveal();
        }

        return new InsertQuery($this->getConnection(true), $stmt, new FilterCollection(), []);
    }
}
