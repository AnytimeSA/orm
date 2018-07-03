<?php

namespace DVE\EntityORM\QueryBuilder;

class DeleteQuery extends QueryAbstract implements DeleteQueryInterface
{
    /**
     * @inheritdoc
     */
    public function execute(): int
    {
        $this->PDOStatement->execute($this->parameters);
        $this->throwPdoError($this->PDOStatement);
        return $this->PDOStatement->rowCount();
    }
}