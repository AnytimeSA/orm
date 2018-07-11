<?php

namespace Anytime\ORM\Generator\EntityGenerator;

class MySqlTableStructureRetriever implements TableStructureRetrieverInterface
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
            $sql = 'SHOW TABLES';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            foreach($stmt->fetchAll() as $elem) {
                $tableList[] = array_values($elem)[0];
            }
        }

        foreach($tableList as $tableName) {
            $result[$tableName] = [
                'structure' => $this->getStructure($tableName),
                'indexes'   => $this->getIndexes($tableName)
            ];
        }

        return $result;
    }

    protected function getStructure(string $tableName): array
    {
        $requiredKeys = ['Field', 'Type', 'Null', 'Key', 'Default'];
        $returnStruct = [];

        $sql = 'DESCRIBE `' . $tableName . '`';
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach($result as $field) {
            foreach($requiredKeys as $requiredKey) {
                if(!array_key_exists($requiredKey, $field)) {
                    throw new \RuntimeException('Missing field structure key "'.$requiredKey.'"');
                }
            }

            $fieldName = $field['Field'];

            if(!array_key_exists($fieldName, $returnStruct)) {
                $returnStruct[$fieldName] = [];
            }

            $returnStruct[$fieldName][] = [
                'fieldName'     =>  $fieldName,
                'type'          =>  $this->mysqlToPhpType($field['Type']),
                'allowNull'     =>  $field['Type'] === 'YES' ? true : false,
                'keyType'       =>  $field['Key'],
                'defaultValue'  =>  $field['Default']
            ];
        }

        return $returnStruct;
    }

    /**
     * @param string $tableName
     * @return array
     */
    protected function getIndexes(string $tableName): array
    {
        $requiredKeys = ['Key_name', 'Column_name', 'Null', 'Index_type'];
        $returnIndexes = [];

        $sql = 'SHOW INDEXES FROM `' . $tableName . '`';
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetchAll();


        foreach($result as $index) {
            foreach($requiredKeys as $requiredKey) {
                if(!array_key_exists($requiredKey, $index)) {
                    throw new \RuntimeException('Missing index structure key "'.$requiredKey.'"');
                }
            }

            $indexName = $index['Key_name'];

            if($indexName != 'PRIMARY') {
                if(!array_key_exists($indexName, $returnIndexes)) {
                    $returnIndexes[$indexName] = [];
                }

                if($index['Index_type'] === 'BTREE') {
                    $returnIndexes[$indexName][] = [
                        'indexName'     => $indexName,
                        'columnName'    => $index['Column_name'],
                        'allowNull'     => $index['Null'] === 'YES' ? true : false
                    ];
                }
            }
        }

        return $returnIndexes;
    }

    /**
     * @param string $mysqlType
     * @return int|string
     */
    protected function mysqlToPhpType(string $mysqlType)
    {
        $patterns = [
            'float'     =>  '(decimal|float|double|real)(.*)',
            'bool'      =>  '(boolean|tinyint\(1\))(.*)',
            'int'       =>  '(int|smallint|mediumint|bigint|tinyint)(.*)'
        ];

        foreach($patterns as $phpType => $pattern) {
            if(preg_match('/^'.$pattern.'$/i', $mysqlType)) {
                return $phpType;
            }
        }

        if(preg_match('/^(date|datetime|year)$/i', $mysqlType)) {
            return 'date';
        }

        return 'string';
    }
}
