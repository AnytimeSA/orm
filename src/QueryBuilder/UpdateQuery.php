<?php

namespace DVE\EntityORM\QueryBuilder;

class UpdateQuery extends QueryAbstract implements UpdateQueryInterface
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