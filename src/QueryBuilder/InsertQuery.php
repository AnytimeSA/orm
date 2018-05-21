<?php

namespace DVE\EntityORM\QueryBuilder;

class InsertQuery extends QueryAbstract implements InsertQueryInterface
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
        return $this->pdo->lastInsertId();
    }
}