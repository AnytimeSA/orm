<?php

namespace Anytime\ORM\Tests\QueryBuilder;

use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\SelectQuery;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\Entity\Foo;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class SelectQueryTest extends ORMTestCase
{

    /**
     * @group QueryBuilder
     * @group SelectQuery
     */
    public function testSetSetters()
    {
        $query = $this->getSelectQuery($this->getPdoStmtMockBuilder()->reveal());
        $this->assertInstanceOf(SelectQuery::class, $query->setFetchDataFormat(SelectQuery::FETCH_DATA_FORMAT_ARRAY));
    }

    /**
     * @group QueryBuilder
     * @group SelectQuery
     */
    public function testFetchMethodReturnsNullIfPDOFetchIsDone()
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilder();
        $stmtMockBuilder->fetch(Argument::any())->willReturn(false);
        $query = $this->getSelectQuery($stmtMockBuilder->reveal());
        $this->assertNull($query->fetch());
        $stmtMockBuilder->closeCursor()->shouldHaveBeenCalled();
    }

    /**
     * @group QueryBuilder
     * @group SelectQuery
     */
    public function testFetchMethodReturnsObjectOfSetEntityClass()
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilderFetchOneResultOnly();

        $query = $this->getSelectQuery($stmtMockBuilder->reveal());
        $query->setEntityClass(Foo::class);
        $fooEntity = $query->fetch();

        $this->assertNotNull($fooEntity);
        $this->assertInstanceOf(Foo::class, $fooEntity);
        $this->assertSame(12, $fooEntity->getId());
        $this->assertSame('abcd', $fooEntity->getSomeField());

        $this->assertNull($query->fetch());

        $stmtMockBuilder->closeCursor()->shouldHaveBeenCalled();
    }

    /**
     * @group testFetchMethodReturnsArrayIfNoEntityClassSet
     */
    public function testFetchMethodReturnsArrayIfNoEntityClassSet()
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilderFetchOneResultOnly();

        $query = $this->getSelectQuery($stmtMockBuilder->reveal());
        $fooData = $query->fetch();

        $this->assertNotNull($fooData);
        $this->assertInternalType('array', $fooData);
        $this->assertArrayHasKey('id', $fooData);
        $this->assertSame(12, $fooData['id']);
        $this->assertArrayHasKey('some_field', $fooData);
        $this->assertSame('abcd', $fooData['some_field']);

        $this->assertNull($query->fetch());

        $stmtMockBuilder->closeCursor()->shouldHaveBeenCalled();

    }

    /**
     * @group QueryBuilder
     * @group SelectQuery
     */
    public function testFetchAllMethodReturnsEmptyArrayIfNoResults()
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilder();
        $stmtMockBuilder->fetchAll(Argument::any())->willReturn([]);
        $query = $this->getSelectQuery($stmtMockBuilder->reveal());
        $results = $query->fetchAll();
        $this->assertInternalType('array', $results);
        $this->assertCount(0, $results);
        $stmtMockBuilder->closeCursor()->shouldHaveBeenCalled();
    }

    /**
     * @group QueryBuilder
     * @group SelectQuery
     */
    public function testFetchAllMethodReturnsArrayOfObjectIfEntityClassIsSet()
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilderFetchMultipleResults();

        $query = $this->getSelectQuery($stmtMockBuilder->reveal());
        $query->setEntityClass(Foo::class);

        $fooEntities = $query->fetchAll();

        $this->assertCount(3, $fooEntities);

        foreach($fooEntities as $fooEntity) {
            $this->assertNotNull($fooEntity);
            $this->assertInstanceOf(Foo::class, $fooEntity);
        }

        $stmtMockBuilder->closeCursor()->shouldHaveBeenCalled();
    }

    /**
     * @group QueryBuilder
     * @group SelectQuery
     */
    public function testFetchAllMethodReturnsArrayofArraysIfNoEntityClassSet()
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilderFetchMultipleResults();

        $query = $this->getSelectQuery($stmtMockBuilder->reveal());

        $fooList = $query->fetchAll();

        $this->assertCount(3, $fooList);

        foreach($fooList as $fooData) {
            $this->assertNotNull($fooData);
            $this->assertInternalType('array', $fooData);
        }

        $stmtMockBuilder->closeCursor()->shouldHaveBeenCalled();
    }

    /**
     * @group QueryBuilder
     * @group SelectQuery
     */
    public function testFetchSingleScalarResultWithIntegerAsValueReturned()
    {
        $valueToReturn = 5;
        $stmtMockBuilder = $this->getPdoStmtMockBuilderFetchSingleScalarResult($valueToReturn);
        $query = $this->getSelectQuery($stmtMockBuilder->reveal());
        $valueReturned = $query->fetchSingleScalarResult();
        $this->assertInternalType('int', $valueReturned);
        $this->assertSame($valueToReturn, $valueReturned);
        $stmtMockBuilder->closeCursor()->shouldHaveBeenCalled();
    }

    /**
     * @group QueryBuilder
     * @group SelectQuery
     */
    public function testFetchSingleScalarResultWithStringAsValueReturned()
    {
        $valueToReturn = 'abc';
        $stmtMockBuilder = $this->getPdoStmtMockBuilderFetchSingleScalarResult($valueToReturn);
        $query = $this->getSelectQuery($stmtMockBuilder->reveal());
        $valueReturned = $query->fetchSingleScalarResult();
        $this->assertInternalType('string', $valueReturned);
        $this->assertSame($valueToReturn, $valueReturned);
        $stmtMockBuilder->closeCursor()->shouldHaveBeenCalled();
    }

    /**
     * @return ObjectProphecy
     */
    private function getPdoStmtMockBuilderFetchOneResultOnly()
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilder();
        $stmtMockBuilder->fetch(Argument::any())->will(function ($args, $mock, $method) {

            $methodCalls = $mock->findProphecyMethodCalls(
                $method->getMethodName(),
                new Argument\ArgumentsWildcard($args)
            );

            return count($methodCalls) === 0
                ? [
                    'id' => 12,
                    'some_field' => 'abcd'
                ]
                : null
                ;
        });
        return $stmtMockBuilder;
    }

    /**
     * @param $valueReturned
     * @return ObjectProphecy
     */
    private function getPdoStmtMockBuilderFetchSingleScalarResult($valueToReturn)
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilder();
        $stmtMockBuilder->fetch(Argument::any())->will(function ($args, $mock, $method) use($valueToReturn) {

            $methodCalls = $mock->findProphecyMethodCalls(
                $method->getMethodName(),
                new Argument\ArgumentsWildcard($args)
            );

            return count($methodCalls) === 0 ? [$valueToReturn] : null;
        });
        return $stmtMockBuilder;
    }

    /**
     * @return ObjectProphecy
     */
    private function getPdoStmtMockBuilderFetchMultipleResults()
    {
        $stmtMockBuilder = $this->getPdoStmtMockBuilder();
        $stmtMockBuilder->fetchAll(Argument::any())->willReturn([
            [
                'id' => 12,
                'some_field' => 'abcd'
            ],
            [
                'id' => 13,
                'some_field' => 'defg'
            ],
            [
                'id' => 14,
                'some_field' => 'higk'
            ]
        ]);
        return $stmtMockBuilder;
    }

    /**
     * @return ObjectProphecy
     */
    private function getPdoStmtMockBuilder()
    {
        $pdoStatementMockBuilder = $this->prophesize(\PDOStatement::class);
        $pdoStatementMockBuilder->rowCount(Argument::any())->willReturn(10);
        $pdoStatementMockBuilder->execute(Argument::any())->willReturn(true);
        $pdoStatementMockBuilder->errorInfo(Argument::any())->willReturn([]);
        $pdoStatementMockBuilder->closeCursor(Argument::any())->willReturn(true);
        return $pdoStatementMockBuilder;
    }

    private function getSelectQuery(\PDOStatement $stmt)
    {
        return new SelectQuery($this->getConnection(true), $stmt, new FilterCollection(), []);
    }
}
