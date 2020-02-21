<?php

namespace Anytime\ORM\Tests\Generator\EntityGenerator;

use Anytime\ORM\Generator\EntityGenerator\PostgreSqlTableStructureRetriever;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PostgreSqlTableStructureRetrieverTest extends TestCase
{
    /**
     * @group Generator
     */
    public function testGetDefaultValueByPhpType()
    {
        $retriever = new PostgreSqlTableStructureRetriever($this->prophesize(\PDO::class)->reveal());
        $this->assertSame(0, $retriever->getDefaultValueByPhpType('int'));
        $this->assertSame(0.0, $retriever->getDefaultValueByPhpType('float'));
        $this->assertSame('1970-01-01 00:00:00.000000 +00:00', $retriever->getDefaultValueByPhpType('date'));
        $this->assertSame(false, $retriever->getDefaultValueByPhpType('bool'));
        $this->assertSame('', $retriever->getDefaultValueByPhpType('string'));
        $this->assertSame(null, $retriever->getDefaultValueByPhpType('unknowntype'));
    }

    /**
     * @group Generator
     */
    public function testGetDateFormatByFieldType()
    {
        $retriever = new PostgreSqlTableStructureRetriever($this->prophesize(\PDO::class)->reveal());

        $this->assertSame('H:i:s.u', $retriever->getDateFormatByFieldType('time'));
        $this->assertSame('H:i:s.u', $retriever->getDateFormatByFieldType('time without time zone'));
        $this->assertSame('H:i:s.u P', $retriever->getDateFormatByFieldType('timetz'));
        $this->assertSame('H:i:s.u P', $retriever->getDateFormatByFieldType('time with time zone'));
        $this->assertSame('Y-m-d H:i:s.u', $retriever->getDateFormatByFieldType('timestamp'));
        $this->assertSame('Y-m-d H:i:s.u', $retriever->getDateFormatByFieldType('timestamp without time zone'));
        $this->assertSame('Y-m-d H:i:s.u P', $retriever->getDateFormatByFieldType('timestamptz'));
        $this->assertSame('Y-m-d H:i:s.u P', $retriever->getDateFormatByFieldType('timestamp with time zone'));
        $this->assertSame('Y-m-d', $retriever->getDateFormatByFieldType('date'));
        $this->assertSame('Y-m-d', $retriever->getDateFormatByFieldType('unknown datetype'));
    }

    /**
     * @group Generator
     * @dataProvider getPgsqlToPhpTypeProvider
     * @param string $fieldType
     * @param $expectedPhpType
     */
    public function testPgsqlToPhpType(string $fieldType, $expectedPhpType)
    {
        $retriever = new PostgreSqlTableStructureRetriever($this->prophesize(\PDO::class)->reveal());
        $this->assertsame($expectedPhpType, $retriever->pgsqlToPhpType($fieldType));
    }

    /**
     * @group Generator
     */
    public function testGetIndexes()
    {
        $returnIndexesQuery = [
            [
                'attname' => 'id',
                'indkey' => '1',
                'relname' => 'id_uindex',
                'indisunique' => true,
                'is_nullable' => 'YES'
            ],
            [
                'attname' => 'field1',
                'indkey' => '2',
                'relname' => 'accounts_acid_uindex',
                'indisunique' => true,
                'is_nullable' => 'NO'
            ],
            [
                'attname' => 'field2',
                'indkey' => '3 4',
                'relname' => 'field2_field3_uindex',
                'indisunique' => true,
                'is_nullable' => 'NO'
            ],
            [
                'attname' => 'field3',
                'indkey' => '3 4',
                'relname' => 'field2_field3_uindex',
                'indisunique' => true,
                'is_nullable' => 'NO'
            ]
        ];

        $pdoMockBuilder = $this->prophesize(\PDO::class);
        $pdoMockBuilder->prepare(Argument::any())->willReturn($this->getPDOStatementMock($returnIndexesQuery));
        $pdo = $pdoMockBuilder->reveal();

        $retriever = new PostgreSqlTableStructureRetriever($pdo);

        $this->assertSame(
            [
                'id_uindex' => [
                    [
                        'tableName' => 'tablename',
                        'indexName' => 'id_uindex',
                        'columnName' => 'id',
                        'allowNull' => true,
                    ]
                ],

                'accounts_acid_uindex' => [
                    [
                        'tableName' => 'tablename',
                        'indexName' => 'accounts_acid_uindex',
                        'columnName' => 'field1',
                        'allowNull' => false,
                    ],
                ],

                'field2_field3_uindex' => [
                    [
                        'tableName' => 'tablename',
                        'indexName' => 'field2_field3_uindex',
                        'columnName' => 'field2',
                        'allowNull' => false,
                    ],
                    [
                        'tableName' => 'tablename',
                        'indexName' => 'field2_field3_uindex',
                        'columnName' => 'field3',
                        'allowNull' => false,
                    ],
                ]
            ],
            $retriever->getIndexes('tablename')
        );
    }

    /**
     * @group Generator
     * @param int $uni
     * @param int $pri
     * @param string $expectedKeyType
     * @dataProvider getKeyTypeProvider
     */
    public function testGetKeyType(int $uni, int $pri, string $expectedKeyType)
    {
        $returnKeyTypeQuery = [
            [
                'is_uni' => $uni,
                'is_pri' => $pri
            ]
        ];

        $pdoStatementMockBuilder = $this->prophesize(\PDOStatement::class);
        $pdoStatementMockBuilder->fetchAll()->willReturn($returnKeyTypeQuery);
        $pdoStatementMockBuilder->execute(Argument::any())->shouldBeCalled();
        $pdoStatement = $pdoStatementMockBuilder->reveal();


        $pdoMockBuilder = $this->prophesize(\PDO::class);
        $pdoMockBuilder->prepare(Argument::any())->willReturn($pdoStatement);
        $pdo = $pdoMockBuilder->reveal();

        $retriever = new PostgreSqlTableStructureRetriever($pdo);

        $this->assertSame($expectedKeyType, $retriever->getKeyType('tablename', 'fieldname'));
    }

    /**
     * @return array
     */
    public function getPgsqlToPhpTypeProvider()
    {
        return [
            ['numeric', 'float'],
            ['float8', 'float'],
            ['double precision', 'float'],
            ['real', 'float'],
            ['float4', 'float'],
            ['bit','bool'],
            ['boolean','bool'],
            ['bool','bool'],
            ['varbit', 'int'],
            ['bit varying', 'int'],
            ['smallint', 'int'],
            ['int2', 'int'],
            ['int', 'int'],
            ['integer', 'int'],
            ['int4', 'int'],
            ['bigint', 'int'],
            ['int8', 'int'],
            ['smallserial', 'int'],
            ['serial2', 'int'],
            ['serial', 'int'],
            ['serial4', 'int'],
            ['bigserial', 'int'],
            ['serial8', 'int'],
            ['date','date'],
            ['timestamptz','date'],
            ['timetz','date'],
            ['time','date'],
            ['timestamp','date'],
            ['time with time zone','date'],
            ['time without time zone','date'],
            ['timestamp with time zone','date'],
            ['timestamp without time zone','date'],
            ['text','string'],
            ['tsquery','string'],
            ['tsvector','string'],
            ['txid_snapshot','string'],
            ['uuid','string'],
            ['xml','string'],
            ['json','string'],
            ['line','string'],
            ['lseg','string'],
            ['macaddr','string'],
            ['inet','string'],
            ['character','string'],
            ['character varying','string'],
            ['cidr','string'],
            ['circle','string'],
            ['box','string'],
            ['bytea','string'],
            ['money','string'],
            ['unknown type', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function getKeyTypeProvider()
    {
        // IS UNI  :  IS PRI  : EXPECTED KEY TYPE
        return [
            [0, 0, 'MUL'],
            [1, 0, 'UNI'],
            [0, 1, 'PRI'],
            [1, 1, 'PRI']
        ];

    }

    protected function getPDOStatementMock(array $returnResult = null)
    {
        $pdoStatementMockBuilder = $this->prophesize(\PDOStatement::class);
        $pdoStatementMockBuilder->fetchAll()->willReturn($returnResult);
        $pdoStatementMockBuilder->setFetchMode(Argument::any())->shouldBeCalled();
        $pdoStatementMockBuilder->execute(Argument::any())->shouldBeCalled();
        $pdoStatement = $pdoStatementMockBuilder->reveal();
        return $pdoStatement;
    }
}
