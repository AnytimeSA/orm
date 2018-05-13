<?php

namespace DVE\EntityORM\QueryBuilder;

class MySqlQueryBuilder extends QueryBuilderAbstract
{
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
    public function getSQL(): string
    {
        $sql  = "SELECT *\n";
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

        // --- ORDER BY
        if($this->orderBy) {
            $sql .= "ORDER BY " . $this->orderBy . "\n";
        }

        // --- LIMIT
        if($this->limitNumber) {
            $sql .= "LIMIT " . $this->limitNumber . " OFFSET " . $this->limitOffset . "\n";
        }

        return $sql;
    }

}