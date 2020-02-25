<?php

namespace Anytime\ORM\Generator\EntityGenerator;

class PostgreSqlTableStructureRetriever extends TableStructureRetrieverAbstract
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * MySqlTableStructureRetriever constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param array $tableList
     * @return array
     */
    public function retrieve(array $tableList = []): array
    {
        $result = [];

        if(count($tableList) < 1) {
            $sql = "SELECT tablename FROM pg_tables WHERE schemaname = 'public';";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            foreach($stmt->fetchAll() as $elem) {
                $tableName = $elem['tablename'];

                $result[$tableName] = [
                    'structure' => $this->getStructure($tableName),
                    'indexes'   => $this->getIndexes($tableName)
                ];
            }
        }


        return $result;
    }

    public function getStructure(string $tableName): array
    {
        $returnStruct = [];

        $sql = "
          SELECT column_name, data_type, is_nullable, column_default 
          FROM INFORMATION_SCHEMA.COLUMNS 
          WHERE table_name = '$tableName' AND table_schema = 'public'
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach($result as $field) {
            $fieldName = $field['column_name'];

            if(!array_key_exists($fieldName, $returnStruct)) {
                $returnStruct[$fieldName] = [];
            }

            $phpType = $this->pgsqlToPhpType($field['data_type']);

            $returnStruct[$fieldName] = [
                'tableName'         =>  $tableName,
                'fieldName'         =>  $fieldName,
                'type'              =>  $phpType,
                'allowNull'         =>  $field['is_nullable'] === 'YES' ? true : false,
                'keyType'           =>  $this->getKeyType($tableName, $fieldName),
                'defaultValue'      =>  $field['column_default'],
                'dateFormat'        =>  $phpType === 'date' ? $this->getDateFormatByFieldType($field['data_type']) : ''
            ];
        }

        return $returnStruct;
    }

    /**
     * Return the key type (PRI, MUL, IND, <null>)
     *
     * @param string $tableName
     * @param string $fieldName
     * @return null|string
     */
    public function getKeyType(string $tableName, string $fieldName)
    {
        $stmt = $this->pdo->prepare("
            SELECT i.indisunique is_uni, i.indisprimary is_pri
            FROM   pg_index i
            JOIN   pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
            WHERE  i.indrelid = '$tableName'::regclass AND a.attname = ?                    
        ");

        $stmt->execute([$fieldName]);

        $results = $stmt->fetchAll();

        if(count($results) > 0) {
            // Check for primary key
            foreach($results as $row) {
                if($row['is_pri']) {
                    return 'PRI';
                }
            }

            // Check for unique key
            foreach($results as $row) {
                if($row['is_uni']) {
                    return 'UNI';
                }
            }

            // Non unique index
            return 'MUL';
        }

        return null;
    }

    /**
     * @param string $tableName
     * @return array
     */
    public function getIndexes(string $tableName): array
    {
        $returnIndexes = [];

        $sql = "
            SELECT a.attname, i.indkey, c.relname, i.indisunique, isc.is_nullable
            FROM   pg_index i
            JOIN   pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
            JOIN   pg_class c ON c.oid = i.indexrelid
            JOIN   INFORMATION_SCHEMA.COLUMNS isc ON isc.column_name = a.attname
            WHERE  i.indrelid = '$tableName'::regclass AND NOT i.indisprimary AND isc.table_schema = 'public';
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach($result as $index) {

            $indexName = $index['relname'];

            if(!array_key_exists($indexName, $returnIndexes)) {
                $returnIndexes[$indexName] = [];
            }

            $returnIndexes[$indexName][] = [
                'tableName'     => $tableName,
                'indexName'     => $indexName,
                'columnName'    => $index['attname'],
                'allowNull'     => $index['is_nullable'] === 'YES' ? true : false
            ];
        }

        return $returnIndexes;
    }

    /**
     * @param string $pgsqlType
     * @return int|string
     */
    public function pgsqlToPhpType(string $pgsqlType): string
    {
        $patterns = [
            'float'     =>  '(numeric|float8|double precision|real|float4)',
            'bool'      =>  '(bit|boolean|bool)',
            'int'       =>  '(varbit|bit varying|smallint|int2|int|integer|int4|bigint|int8|smallserial|serial2|serial|serial4|bigserial|serial8)',
            'date'      =>  '(date|timestamptz|timetz|time|timestamp|time with time zone|time without time zone|timestamp with time zone|timestamp without time zone)'
        ];

        foreach($patterns as $phpType => $pattern) {
            if(preg_match('/^'.$pattern.'$/i', $pgsqlType)) {
                return $phpType;
            }
        }

        return 'string';
    }

    /**
     * @param string $fieldType
     * @return string
     */
    public function getDateFormatByFieldType(string $fieldType): string
    {
        $format = 'Y-m-d';

        switch($fieldType) {
            case 'time':
            case 'time without time zone':
                $format = 'H:i:s.u';
                break;
            case 'timetz':
            case 'time with time zone':
                $format = 'H:i:s.u P';
                break;
            case 'timestamp':
            case 'timestamp without time zone':
                $format = 'Y-m-d H:i:s.u';
                break;
            case 'timestamptz':
            case 'timestamp with time zone':
                $format = 'Y-m-d H:i:s.u P';
                break;
            case 'date': $format = 'Y-m-d'; break;
        }

        return $format;
    }
}
