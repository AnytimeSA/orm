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
     * @group testGetStructureReturnsSeveralFields
     */
    public function testGetStructureReturnsSeveralFields()
    {
        $returnStructureQuery = [
            [
                'column_name' => 'id',
                'data_type' => 'bigint',
                'is_nullable' => 'NO',
                'column_default' => 'nextval(\'random_table_id_seq\'::regclass)'
            ],
            [
                'column_name' => 'name',
                'data_type' => 'character varying',
                'is_nullable' => 'YES',
                'column_default' => null
            ],
            [
                'column_name' => 'price',
                'data_type' => 'real',
                'is_nullable' => 'NO',
                'column_default' => 0.0
            ]
        ];

        $pdoMockBuilder = $this->prophesize(\PDO::class);
        $pdoMockBuilder->prepare(Argument::any())->willReturn(
            $this->getPDOStatementMock($returnStructureQuery),
            $this->getPDOStatementMock([], false),
            $this->getPDOStatementMock([], false),
            $this->getPDOStatementMock([], false)
        );
        $pdo = $pdoMockBuilder->reveal();

        $retriever = new PostgreSqlTableStructureRetriever($pdo);

        $this->assertSame(
            [
                'id' => [
                    'tableName' => 'random_table',
                    'fieldName' => 'id',
                    'type' => 'int',
                    'allowNull' => false,
                    'keyType' => null,
                    'defaultValue' => null,
                    'dateFormat' => ''
                ],
                'name' => [
                    'tableName' => 'random_table',
                    'fieldName' => 'name',
                    'type' => 'string',
                    'allowNull' => true,
                    'keyType' => null,
                    'defaultValue' => null,
                    'dateFormat' => ''
                ],
                'price' => [
                    'tableName' => 'random_table',
                    'fieldName' => 'price',
                    'type' => 'float',
                    'allowNull' => false,
                    'keyType' => null,
                    'defaultValue' => null,
                    'dateFormat' => ''
                ]
            ],
            $retriever->getStructure('random_table')
        );
    }

    /**
     * @group Generator
     * @dataProvider getGetStructureAllCases
     * @param $dataType
     * @param $isNullable
     * @param $defaultFieldVal
     * @param $expectType
     * @param $expectAllowNull
     * @param $expectDefaultPropertyVal
     * @param $expectDateFormat
     * @group testGetStructureAllCases
     */
    public function testGetStructureAllCases($dataType, $isNullable, $defaultFieldVal, $expectType, $expectAllowNull, $expectDefaultPropertyVal, $expectDateFormat)
    {
        $pdoMockBuilder = $this->prophesize(\PDO::class);
        $pdoMockBuilder->prepare(Argument::any())->willReturn(
            $this->getPDOStatementMock([
                [
                    'column_name' => 'col_name',
                    'data_type' => $dataType,
                    'is_nullable' => $isNullable,
                    'column_default' => $defaultFieldVal
                ]
            ]),
            $this->getPDOStatementMock([], false)
        );
        $pdo = $pdoMockBuilder->reveal();

        $retriever = new PostgreSqlTableStructureRetriever($pdo);

        $this->assertSame(
            [
                'col_name' => [
                    'tableName' => 'random_table',
                    'fieldName' => 'col_name',
                    'type' => $expectType,
                    'allowNull' => $expectAllowNull,
                    'keyType' => null,
                    'defaultValue' => $expectDefaultPropertyVal,
                    'dateFormat' => $expectDateFormat
                ]
            ],
            $retriever->getStructure('random_table')
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

    /**
     * @param array|null $returnResult
     * @param bool $callSetFetchMode
     * @return object
     */
    protected function getPDOStatementMock(array $returnResult = null, $callSetFetchMode = true)
    {
        $pdoStatementMockBuilder = $this->prophesize(\PDOStatement::class);
        $pdoStatementMockBuilder->fetchAll()->willReturn($returnResult);

        if($callSetFetchMode) {
            $pdoStatementMockBuilder->setFetchMode(Argument::any())->shouldBeCalled();
        }

        $pdoStatementMockBuilder->execute(Argument::any())->shouldBeCalled();
        $pdoStatement = $pdoStatementMockBuilder->reveal();
        return $pdoStatement;
    }

    /**
     * @return array
     */
    public function getGetStructureAllCases()
    {
        //  $dataType, $isNullable, $defaultSqlFieldValue, $expectType, $expectAllowNull, $defaultPropertyValue, $expectDateFormat
        return [
            ['numeric', 'NO', 0.0, 'float', false, null, ''],
            ['float4', 'NO', 0.0, 'float', false, null, ''],
            ['float8', 'NO', 0.0, 'float', false,null,  ''],
            ['double precision', 'NO', 0.0, 'float', false, null, ''],
            ['real', 'NO', 0.0, 'float', false, null, ''],
            ['numeric', 'NO', null, 'float', false, 0.0, ''],
            ['numeric', 'YES', null, 'float', true, null, ''],
            ['numeric', 'YES', 0.0, 'float', true, null, ''],
            ['bit', 'NO', "'0'::\"bit\"", 'bool', false, null, ''],
            ['bit', 'YES', "'0'::\"bit\"", 'bool', true, null, ''],
            ['bit', 'NO', null, 'bool', false, false, ''],
            ['bool', 'NO', false, 'bool', false, null, ''],
            ['bool', 'YES', false, 'bool', true, null, ''],
            ['bool', 'NO', true, 'bool', false, null, ''],
            ['bool', 'YES', true, 'bool', true, null, ''],
            ['bool', 'NO', null, 'bool', false, false, ''],
            ['bool', 'YES', null, 'bool', true, null, ''],
            ['boolean', 'NO', false, 'bool', false, null, ''],
            ['varbit', 'YES', 10, 'int', true, null, ''],
            ['bit varying', 'YES', 10, 'int', true, null, ''],
            ['smallint', 'YES', 10, 'int', true, null, ''],
            ['int2', 'YES', 10, 'int', true, null, ''],
            ['int', 'YES', 10, 'int', true, null, ''],
            ['integer', 'YES', 10, 'int', true, null, ''],
            ['int4', 'YES', 10, 'int', true, null, ''],
            ['bigint', 'YES', 10, 'int', true, null, ''],
            ['int8', 'YES', 10, 'int', true, null, ''],
            ['smallserial', 'YES', 10, 'int', true, null, ''],
            ['serial2', 'YES', 10, 'int', true, null, ''],
            ['serial', 'YES', 10, 'int', true, null, ''],
            ['serial4', 'YES', 10, 'int', true, null, ''],
            ['bigserial', 'YES', 10, 'int', true, null, ''],
            ['serial8', 'YES', 10, 'int', true, null, ''],
            ['varbit', 'NO', 10, 'int', false, null, ''],
            ['bit varying', 'NO', 10, 'int', false, null, ''],
            ['smallint', 'NO', 10, 'int', false, null, ''],
            ['int2', 'NO', 10, 'int', false, null, ''],
            ['int', 'NO', 10, 'int', false, null, ''],
            ['integer', 'NO', 10, 'int', false, null, ''],
            ['int4', 'NO', 10, 'int', false, null, ''],
            ['bigint', 'NO', 10, 'int', false, null, ''],
            ['int8', 'NO', 10, 'int', false, null, ''],
            ['smallserial', 'NO', 10, 'int', false, null, ''],
            ['serial2', 'NO', 10, 'int', false, null, ''],
            ['serial', 'NO', 10, 'int', false, null, ''],
            ['serial4', 'NO', 10, 'int', false, null, ''],
            ['bigserial', 'NO', 10, 'int', false, null, ''],
            ['serial8', 'NO', 10, 'int', false, null, ''],
            ['serial8', 'NO', null, 'int', false, 0, ''],
            ['date', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'Y-m-d'],
            ['timestamptz', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'Y-m-d H:i:s.u P'],
            ['timetz', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'H:i:s.u P'],
            ['time', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'H:i:s.u'],
            ['timestamp', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'Y-m-d H:i:s.u'],
            ['time with time zone', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'H:i:s.u P'],
            ['time without time zone', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'H:i:s.u'],
            ['timestamp with time zone', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'Y-m-d H:i:s.u P'],
            ['timestamp without time zone', 'NO', null, 'date', false, '1970-01-01 00:00:00.000000 +00:00', 'Y-m-d H:i:s.u'],
            ['timestamp without time zone', 'YES', null, 'date', true, null, 'Y-m-d H:i:s.u'],
            ['timestamp without time zone', 'YES', '2020-11-01 14:11:54.000000 +00:01', 'date', true, null, 'Y-m-d H:i:s.u']
        ];
    }
}
