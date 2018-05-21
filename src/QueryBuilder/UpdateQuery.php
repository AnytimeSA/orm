<?php

namespace DVE\EntityORM\QueryBuilder;

class UpdateQuery extends QueryAbstract implements UpdateQueryInterface
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