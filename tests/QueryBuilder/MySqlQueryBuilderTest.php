<?php

namespace Anytime\ORM\Tests\QueryBuilder;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\QueryBuilder\MySqlQueryBuilder;
use PHPUnit\Framework\TestCase;

class MySqlQueryBuilderTest extends TestCase
{
    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithoutAnyDefinedClause()
    {
        $this->expectException(\RuntimeException::class);
        $this->getQueryBuilder()->getSelectSQL();
    }

    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithFromClause()
    {
        $this->assertSame(
            'SELECT * FROM `test_table`',
            $this->getQueryBuilder()->from('test_table')->getSelectSQL()
        );

        $this->assertSame(
            'SELECT * FROM `test_table` AS `tt`',
            $this->getQueryBuilder()->from('test_table', 'tt')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithOrderClause()
    {
        $this->assertSame(
            'SELECT * FROM `test_table` ORDER BY myfield DESC',
            $this->getQueryBuilder()->from('test_table')->orderBy('myfield DESC')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithGroupByClause()
    {
        $this->assertSame(
            'SELECT * FROM `test_table` GROUP BY groupfield, groupfield2',
            $this->getQueryBuilder()->from('test_table')->groupBy('groupfield, groupfield2')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithLimitClause()
    {
        $queryBuilder = $this->getQueryBuilder()->from('test_table');
        $this->assertSame('SELECT * FROM `test_table` LIMIT 10 OFFSET 0', $queryBuilder->limit(10, 0)->getSelectSQL());
        $this->assertSame('SELECT * FROM `test_table` LIMIT 10 OFFSET 50', $queryBuilder->limit(10, 50)->getSelectSQL());
        $this->assertSame('SELECT * FROM `test_table` LIMIT 1 OFFSET 0', $queryBuilder->limit(0, 0)->getSelectSQL());
        $this->assertSame('SELECT * FROM `test_table` LIMIT 1 OFFSET 0', $queryBuilder->limit(-1, 0)->getSelectSQL());
        $this->assertSame('SELECT * FROM `test_table` LIMIT 1 OFFSET 0', $queryBuilder->limit(-1, -1)->getSelectSQL());
    }

    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithOrderGroupByClause()
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->assertSame(
            'SELECT * FROM `test_table` GROUP BY groupfield, groupfield2',
            $queryBuilder->from('test_table')->groupBy('groupfield, groupfield2')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithOrderGroupByAndLimitClause()
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->assertSame(
            'SELECT * FROM `test_table` GROUP BY groupfield, groupfield2 ORDER BY myfield DESC',
            $queryBuilder
                ->from('test_table')
                ->orderBy('myfield DESC')
                ->groupBy('groupfield, groupfield2')
                ->getSelectSQL()
        );

        $this->assertSame(
            'SELECT * FROM `test_table` GROUP BY groupfield, groupfield2 ORDER BY myfield DESC LIMIT 10 OFFSET 0',
            $queryBuilder->limit(10, 0)->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithWhereClause()
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->assertSame(
            'SELECT * FROM `test_table` AS `tt` WHERE (tt.myfield = ?)',
            $queryBuilder->from('test_table', 'tt')->where('tt.myfield = ?')->getSelectSQL()
        );

        $this->assertSame(
            'SELECT * FROM `test_table` AS `tt` WHERE (tt.myfield = ?) AND (tt.myotherfield = 2000)',
            $queryBuilder->andWhere('tt.myotherfield = 2000')->getSelectSQL()
        );

        $this->assertSame(
            'SELECT * FROM `test_table` AS `tt` WHERE (tt.myfield = ?) AND (tt.myotherfield = 2000) ' .
            'AND (tt.againotherfield = ? OR tt.againotherfield = ?)',
            $queryBuilder->andWhere('tt.againotherfield = ? OR tt.againotherfield = ?')->getSelectSQL()
        );
    }

    /**
     * @group QueryBuilder
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWithJoinClause()
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->assertSame(
            'SELECT * FROM `test_table` AS `tt`' .
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
     * @group MySqlQueryBuilder
     */
    public function testGetSelectSQLWHenChangingSelectClause()
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->assertSame(
            'SELECT COUNT(id) AS `qty` FROM `test_table` AS `tt`',
            $queryBuilder
                ->select('COUNT(id) AS `qty`')
                ->from('test_table', 'tt')
                ->getSelectSQL()
        );
    }

    /**
     * @return MySqlQueryBuilder
     */
    private function getQueryBuilder(): MySqlQueryBuilder
    {
        $connection = $this->prophesize(Connection::class)->reveal();
        $queryBuilder = new MySqlQueryBuilder($connection, new SnakeToCamelCaseStringConverter());
        return $queryBuilder;
    }
}
