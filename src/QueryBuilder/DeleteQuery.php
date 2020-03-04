<?php

namespace Anytime\ORM\QueryBuilder;

class DeleteQuery extends QueryAbstract implements DeleteQueryInterface
{
    /**
     * @inheritdoc
     */
    public function execute(): int
    {
        $this->bindParams();
        $this->PDOStatement->execute();
        $this->throwPdoError($this->PDOStatement);
        return $this->PDOStatement->rowCount();
    }
}
