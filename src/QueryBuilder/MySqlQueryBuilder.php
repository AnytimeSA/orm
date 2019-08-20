<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\QueryBuilder\Expression\Expr;

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
        if(!$this->from) {
            throw new \RuntimeException('No table defined in FROM clause. Please use "from" method.');
        }

        $sql  = 'SELECT ' . $this->select;
        $sql .= ' FROM ' . $this->from;

        // --- JOIN
        foreach($this->join as $join) {
            $sql .= ' ' . $join;
        }

        // --- WHERE
        if(count($this->where) > 0) {
            $sql .= ' WHERE';
            foreach($this->where as $iw => $where) {
                $sql .= ' ' . ($iw > 0 ? 'AND ' : '') . "($where)";
            }
        }

        // --- GROUP BY
        if ($this->groupBy) {
            $sql .= ' GROUP BY ' . $this->groupBy;
        }

        // --- ORDER BY
        if ($this->orderBy) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }

        // --- LIMIT
        if ($this->limitNumber) {
            $sql .= ' LIMIT ' . $this->limitNumber . ' OFFSET ' . $this->limitOffset;
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

    /**
     * @param array $fields
     * @return string
     */
    public function getUpdateByPrimaryKeySQL(array $fields): string
    {
        $this->checkUpdateFieldsArray($fields);

        $tableName = $this->entityClass::TABLENAME;
        $primaryKeys = $this->entityClass::PRIMARY_KEYS;

        $sql = "UPDATE `$tableName`";
        $sqlSet = '';

        foreach($fields as $fieldName => $value) {
            $this->checkUpdateFieldName($fieldName);
            $sqlSet .= ($sqlSet ? ",\n" : '') . "`$fieldName` = :UPDATE_VALUE_$fieldName";
        }
        $sqlSet = " SET \n" . $sqlSet . " ";

        $sqlWhere = '';
        foreach($primaryKeys as $pkeyName) {
            $sqlWhere .= ($sqlWhere ? ' AND ' : 'WHERE ') . "`$pkeyName` = :$pkeyName\n";
        }

        return $sql . $sqlSet . $sqlWhere . ';';
    }

    /**
     * @param array $fields
     * @return string
     */
    public function getUpdateByCriteriaSQL(array $fields): string
    {
        $this->checkUpdateFieldsArray($fields);

        $tableName = $this->entityClass::TABLENAME;

        $sql = "UPDATE `$tableName`";
        $sqlWhere = '';
        $sqlSet = '';

        foreach($fields as $fieldName => $value) {
            $this->checkUpdateFieldName($fieldName);

            if($value instanceof Expr) {
                $sqlSet .= ($sqlSet ? ",\n" : '') . "`$fieldName` = " . $value->getExpr($fieldName, '`');
            } else {
                $sqlSet .= ($sqlSet ? ",\n" : '') . "`$fieldName` = :UPDATE_VALUE_$fieldName";
            }
        }
        $sqlSet = " SET \n" . $sqlSet;

        // --- WHERE
        if(count($this->where) > 0) {
            $sqlWhere .= " WHERE \n";
            foreach($this->where as $iw => $where) {
                $sqlWhere .= ($iw > 0 ? ' AND ' : '') . "($where)\n";
            }
        }

        return $sql . $sqlSet . $sqlWhere . ';';
    }

    /**
     * @return string
     */
    public function getDeleteByPrimaryKeySQL(): string
    {
        $tableName = $this->entityClass::TABLENAME;
        $primaryKeys = $this->entityClass::PRIMARY_KEYS;

        $sql = "DELETE FROM `$tableName`\n";
        $sqlWhere = '';

        foreach($primaryKeys as $pkeyName) {
            $sqlWhere .= ($sqlWhere ? ' AND ' : 'WHERE ') . "`$pkeyName` = :$pkeyName\n";
        }

        return $sql . $sqlWhere . ';';
    }

    /**
     * @return string
     */
    public function getDeleteByCriteriaSQL(): string
    {
        $tableName = $this->entityClass::TABLENAME;

        $sql = "DELETE FROM `$tableName`\n";

        // --- WHERE
        if(count($this->where) > 0) {
            $sql .= " WHERE \n";
            foreach($this->where as $iw => $where) {
                $sql .= ($iw > 0 ? ' AND ' : '') . "($where)\n";
            }
        }

        return $sql;
    }

    /**
     * @inheritdoc
     */
    public function getFindByPrimaryKeySQLWhere(array $primaryKeys): string
    {
        $where = '';
        $tableName = $this->entityClass::TABLENAME;

        foreach($primaryKeys as $primaryKey) {
            $where .= ($where ? ' AND ' : ''). '`' . $tableName.'`.`'.$primaryKey . '` = ?';
        }

        return $where;
    }

    /**
     * @param array $fields
     */
    private function checkUpdateFieldsArray(array $fields)
    {
        if(count($fields) < 1) {
            throw new \InvalidArgumentException('Update methods require an non-empty array containing the list of fields to update as first argument.');
        }
    }

    /**
     * @param string $fieldName
     */
    private function checkUpdateFieldName($fieldName)
    {
        if(is_numeric($fieldName)) {
            throw new \InvalidArgumentException('Invalid field name "'.$fieldName.'".');
        }
    }
}
