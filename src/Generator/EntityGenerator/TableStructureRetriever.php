<?php

namespace DVE\EntityORM\Generator\EntityGenerator;

class TableStructureRetriever implements TableStructureRetrieverInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * TableStructureRetriever constructor.
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
                $tableList[] = $elem[0];
            }
        }

        foreach($tableList as $tableName) {
            $sql = 'DESCRIBE `' . $tableName . '`';
            $stmt = $this->pdo->prepare($sql);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $stmt->execute();

            $result[$tableName] = $stmt->fetchAll();
        }

        return $result;
    }
}