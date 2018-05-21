<?php

namespace DVE\EntityORM\QueryBuilder;

class MySqlQueryBuilder extends QueryBuilderAbstract
{
    /**
     * @inheritDoc
     */
    protected $select = '*';

    /**
     * @inheritDoc
     */
    public function from(string $tableName, $alias = null): QueryBuilderInterface
    {
        $this->from = "`$tableName`".($alias ? " AS `$alias`": '');
        return $this;
    }

    /**
     * @return string
     */
    public function getSelectSQL(): string
    {
        $sql  = "SELECT " . $this->select . "\n";
        $sql .= "FROM " . $this->from . "\n";

        // --- JOIN
        foreach($this->join as $join) {
            $sql .= $join . "\n";
        }

        // --- WHERE
        if(count($this->where) > 0) {
            $sql .= "WHERE \n";
            foreach($this->where as $iw => $where) {
                $sql .= ($iw > 0 ? ' AND ' : '') . "($where)\n";
            }
        }

        // --- GROUP BY
        if ($this->groupBy) {
            $sql .= "GROUP BY " . $this->groupBy . "\n";
        }

        // --- ORDER BY
        if ($this->orderBy) {
            $sql .= "ORDER BY " . $this->orderBy . "\n";
        }

        // --- LIMIT
        if ($this->limitNumber) {
            $sql .= "LIMIT " . $this->limitNumber . " OFFSET " . $this->limitOffset . "\n";
        }

        return $sql;
    }

    /**
     * @param array $fields
     * @return string
     */
    public function getInsertSQL(array $fields): string
    {
        $tableName = $this->entityClass::TABLENAME;

        $sql = "INSERT INTO `$tableName`\n";
        $sqlFields = '';
        $sqlValues = '';

        foreach($fields as $fieldName => $value) {
            $sqlFields .= ($sqlFields ? ",\n" : '') . "`$fieldName`";
            $sqlValues .= ($sqlValues ? ",\n" : '') . ":$fieldName";
        }

        $sql .= "($sqlFields) VALUES ($sqlValues);";

        return $sql;
    }

    public function getUpdateSQL(array $fields): string
    {
        // TODO: Implement getUpdateSQL() method.
    }

    public function getDeleteSQL(array $fields): string
    {
        // TODO: Implement getDeleteSQL() method.
    }




}