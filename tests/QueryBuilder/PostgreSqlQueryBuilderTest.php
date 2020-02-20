<?php

namespace Anytime\ORM\Tests\QueryBuilder;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\PostgreSqlQueryBuilder;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\Entity\Foo;

class PostgreSqlQueryBuilderTest extends ORMTestCase
{
    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWithoutAnyDefinedClause()
    {
        $this->expectException(\RuntimeException::class);
        $this->getQueryBuilder()->getSelectSQL();
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWithFromClause()
    {
        $this->assertSameSQL(
            'SELECT * FROM test_table',
            $this->getQueryBuilder()->from('test_table')->getSelectSQL()
        );

        $this->assertSameSQL(
            'SELECT * FROM test_table AS tt',
            $this->getQueryBuilder()->from('test_table', 'tt')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWithOrderClause()
    {
        $this->assertSameSQL(
            'SELECT * FROM test_table ORDER BY myfield DESC',
            $this->getQueryBuilder()->from('test_table')->orderBy('myfield DESC')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWithGroupByClause()
    {
        $this->assertSameSQL(
            'SELECT * FROM test_table GROUP BY groupfield, groupfield2',
            $this->getQueryBuilder()->from('test_table')->groupBy('groupfield, groupfield2')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     * @group testGetSelectSQLWithLimitClause
     */
    public function testGetSelectSQLWithLimitClause()
    {
        $queryBuilder = $this->getQueryBuilder()->from('test_table');
        $this->assertSameSQL('SELECT * FROM test_table LIMIT 10 OFFSET 0', $queryBuilder->limit(10, 0)->getSelectSQL());
        $this->assertSameSQL('SELECT * FROM test_table LIMIT 10 OFFSET 50', $queryBuilder->limit(10, 50)->getSelectSQL());
        $this->assertSameSQL('SELECT * FROM test_table', $queryBuilder->limit(0, 0)->getSelectSQL());
        $this->assertSameSQL('SELECT * FROM test_table', $queryBuilder->limit(-1, 0)->getSelectSQL());
        $this->assertSameSQL('SELECT * FROM test_table', $queryBuilder->limit(-1, -1)->getSelectSQL());
        $this->assertSameSQL('SELECT * FROM test_table LIMIT ALL OFFSET 10', $queryBuilder->limit(0, 10)->getSelectSQL());
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWithOrderGroupByClause()
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->assertSameSQL(
            'SELECT * FROM test_table GROUP BY groupfield, groupfield2',
            $queryBuilder->from('test_table')->groupBy('groupfield, groupfield2')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWithOrderGroupByAndLimitClause()
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->assertSameSQL(
            'SELECT * FROM test_table GROUP BY groupfield, groupfield2 ORDER BY myfield DESC',
            $queryBuilder
                ->from('test_table')
                ->orderBy('myfield DESC')
                ->groupBy('groupfield, groupfield2')
                ->getSelectSQL()
        );

        $this->assertSameSQL(
            'SELECT * FROM test_table GROUP BY groupfield, groupfield2 ORDER BY myfield DESC LIMIT 10 OFFSET 0',
            $queryBuilder->limit(10, 0)->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWithWhereClause()
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->assertSameSQL(
            'SELECT * FROM test_table AS tt WHERE (tt.myfield = ?)',
            $queryBuilder->from('test_table', 'tt')->where('tt.myfield = ?')->getSelectSQL()
        );

        $this->assertSameSQL(
            'SELECT * FROM test_table AS tt WHERE (tt.myfield = ?) AND (tt.myotherfield = 2000)',
            $queryBuilder->andWhere('tt.myotherfield = 2000')->getSelectSQL()
        );

        $this->assertSameSQL(
            'SELECT * FROM test_table AS tt WHERE (tt.myfield = ?) AND (tt.myotherfield = 2000) ' .
            'AND (tt.againotherfield = ? OR tt.againotherfield = ?)',
            $queryBuilder->andWhere('tt.againotherfield = ? OR tt.againotherfield = ?')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     * @group testGetInsertSQLWithEmptyArrayKeyValuesThrowsException
     */
    public function testGetInsertSQLWithEmptyArrayKeyValuesThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Update and insert methods require an non-empty array containing the list of fields to update as first argument.');

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_INSERT)
            ->setEntityClass(Foo::class)
        ;
        $queryBuilder->getInsertSQL([]);
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetInsertSQLWithInvalidFieldsKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field name "0".');

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_INSERT)
            ->setEntityClass(Foo::class)
        ;
        $queryBuilder->getInsertSQL([
            'invalid',
            'keys'
        ]);
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWithJoinClause()
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->assertSameSQL(
            'SELECT * FROM test_table AS tt' .
            ' LEFT JOIN table2 t2 ON tt.idt2 = t2.id' .
            ' LEFT JOIN table3 t3 ON tt.idt3 = t3.id',
            $queryBuilder
                ->from('test_table', 'tt')
                ->join('LEFT JOIN table2 t2 ON tt.idt2 = t2.id')
                ->join('LEFT JOIN table3 t3 ON tt.idt3 = t3.id')
                ->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetSelectSQLWHenChangingSelectClause()
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->assertSameSQL(
            'SELECT COUNT(id) AS qty FROM test_table AS tt',
            $queryBuilder
                ->select('COUNT(id) AS qty')
                ->from('test_table', 'tt')
                ->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetUpdateByCriteriaSQLThrowsExceptionWithEmptyArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Update and insert methods require an non-empty array containing the list of fields to update as first argument.');
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)
            ->setEntityClass(Foo::class)
        ;
        $queryBuilder->getUpdateByCriteriaSQL([]);
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetUpdateByCriteriaSQLThrowsExceptionWithNumericKeys()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field name "0".');
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)
            ->setEntityClass(Foo::class)
        ;
        $queryBuilder->getUpdateByCriteriaSQL(['aaa', 'field' => 'val']);
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetUpdateByCriteriaSQLSucceedWithValidKeyValueArray()
    {
        $fields = [
            'field1' => 'val1',
            'field2' => 'val2'
        ];

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)
            ->setEntityClass(Foo::class)
        ;

        $this->assertSameSQL(
            'UPDATE foo_entity SET field1 = :UPDATE_VALUE_field1,field2 = :UPDATE_VALUE_field2;',
            $queryBuilder->getUpdateByCriteriaSQL($fields)
        );

        $queryBuilder->where('field3 = :val3')->setParameter('val3', '3')->andWhere('field4 = field5');

        $this->assertSameSQL(
            'UPDATE foo_entity SET field1 = :UPDATE_VALUE_field1,field2 = :UPDATE_VALUE_field2 WHERE (field3 = :val3) AND (field4 = field5);',
            $queryBuilder->getUpdateByCriteriaSQL($fields)
        );

    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetUpdateByPrimaryKeySQLThrowsExceptionWithEmptyArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Update and insert methods require an non-empty array containing the list of fields to update as first argument.');
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)
            ->setEntityClass(Foo::class)
        ;
        $queryBuilder->getUpdateByPrimaryKeySQL([]);
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetUpdateByPrimaryKeySQLThrowsExceptionWithNumericKeys()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field name "0".');
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)
            ->setEntityClass(Foo::class)
        ;
        $queryBuilder->getUpdateByPrimaryKeySQL([
            'val1'
        ]);
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetUpdateByPrimaryKeySQLSucceedWithValidKeyValueArray()
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)
            ->setEntityClass(Foo::class)
        ;
        $this->assertSameSQL(
            'UPDATE foo_entity SET field1 = :UPDATE_VALUE_field1,field2 = :UPDATE_VALUE_field2 WHERE id = :id;',
            $queryBuilder->getUpdateByPrimaryKeySQL([
                'field1' => 'val1',
                'field2' => 'val2',
            ])
        );
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetDeleteByPrimaryKeySQL()
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)
            ->setEntityClass(Foo::class)
        ;
        $this->assertSameSQL('DELETE FROM foo_entity WHERE id = :id;', $queryBuilder->getDeleteByPrimaryKeySQL());
    }

    /**
     * @group QueryBuilder
     * @group PostgreSqlQueryBuilder
     */
    public function testGetDeleteByCriteriaSQL()
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_DELETE)
            ->setEntityClass(Foo::class)
        ;

        $this->assertSameSQL(
            "DELETE FROM foo_entity",
            $queryBuilder->getDeleteByCriteriaSQL()
        );

        $queryBuilder
            ->setParameter('baz', 2)
            ->where('foo.bar = 1 AND foo.baz = :baz')
        ;

        $this->assertSameSQL(
            "DELETE FROM foo_entity WHERE (foo.bar = 1 AND foo.baz = :baz)",
            $queryBuilder->getDeleteByCriteriaSQL()
        );
    }

    /**
     * @return PostgreSqlQueryBuilder
     */
    private function getQueryBuilder(): PostgreSqlQueryBuilder
    {
        $connection = $this->prophesize(Connection::class)->reveal();
        $queryBuilder = new PostgreSqlQueryBuilder($connection, new SnakeToCamelCaseStringConverter(), new FilterCollection());
        return $queryBuilder;
    }
}
