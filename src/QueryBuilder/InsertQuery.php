<?php

namespace Anytime\ORM\QueryBuilder;

class InsertQuery extends QueryAbstract implements InsertQueryInterface
{
    /**
     * @inheritdoc
     */
    public function execute(): int
    {
        $this->bindParams();
        $this->PDOStatement->execute();
        $this->throwPdoError($this->PDOStatement);
        return $this->connection->lastInsertId();
    }
}
