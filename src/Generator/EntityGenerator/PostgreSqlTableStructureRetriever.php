<?php

namespace Anytime\ORM\Generator\EntityGenerator;

class PostgreSqlTableStructureRetriever implements TableStructureRetrieverInterface
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
            $sql = "SELECT * FROM pg_tables WHERE schemaname = 'public';";
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

    protected function getStructure(string $tableName): array
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
                'defaultValue'      =>  (
                    substr(explode('::', $field['column_default'])[0], 0, 1) === "'" && substr(explode('::', $field['column_default'])[0], strlen(explode('::', $field['column_default'])[0])-1, 1) === "'"
                        ? trim(explode('::', $field['column_default'])[0], "'")
                        : (
                            $field['is_nullable'] === 'YES'
                                ? null
                                : $this->getDefaultValueByPhpType($phpType)
                        )
                ),

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
    protected function getKeyType(string $tableName, string $fieldName)
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
    protected function getIndexes(string $tableName): array
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
    protected function pgsqlToPhpType(string $pgsqlType): string
    {
        $patterns = [
            'float'     =>  '(numeric|float8|double precision|real|float4)',
            'bool'      =>  '(bit|boolean|bool)',
            'int'       =>  '(varbit|bit varying|smallint|int2|int|integer|int4|bigint|int8|smallserial|serial2|serial|serial4|bigserial|serial8)'
        ];

        foreach($patterns as $phpType => $pattern) {
            if(preg_match('/^'.$pattern.'$/i', $pgsqlType)) {
                return $phpType;
            }
        }

        if(preg_match('/^(date|timestamptz|timetz|time|timestamp)$/i', $pgsqlType)) {
            return 'date';
        }

        return 'string';
    }

    /**
     * @param string $fieldType
     * @return string
     */
    protected function getDateFormatByFieldType(string $fieldType): string
    {
        switch($fieldType) {
            case 'time': return "H:i:s.u";
            case 'timetz': return "H:i:s.u P";
            case 'timestamp': return 'Y-m-d H:i:s.u';
            case 'timestamptz': return 'Y-m-d H:i:s.u P';
            case 'date': return 'Y-m-d';
        }
        return '';
    }

    /**
     * @param string $phpType
     * @return bool|float|int|null|string
     */
    protected function getDefaultValueByPhpType(string $phpType)
    {
        switch($phpType) {
            case 'int': return 0;
            case 'float': return 0.0;
            case 'bool': return false;
            case 'date': return '1970-01-01 00:00:00.000000 +00:00';
            case 'string': return '';
            default: return null;
        }
    }
}
