<?php

namespace DVE\EntityORM\QueryBuilder;

class DeleteQuery extends QueryAbstract implements DeleteQueryInterface
{
    /**
     * @inheritdoc
     */
    public function execute(): int
    {
        if(!$this->PDOStatement->execute($this->parameters)) {
            $errInfo = implode(' ; ', $this->PDOStatement->errorInfo());
            $errCode = $this->PDOStatement->errorCode();
            throw new \RuntimeException('Err #' . $errCode . ' - ' . $errInfo);
        }
        return $this->PDOStatement->rowCount();
    }
}