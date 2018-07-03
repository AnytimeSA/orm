<?php

namespace DVE\EntityORM\QueryBuilder;

class InsertQuery extends QueryAbstract implements InsertQueryInterface
{
    /**
     * @inheritdoc
     */
    public function execute(): int
    {
        $this->PDOStatement->execute($this->parameters);
        $this->throwPdoError($this->PDOStatement);
        return $this->pdo->lastInsertId();
    }
}