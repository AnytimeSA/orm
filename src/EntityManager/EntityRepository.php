<?php

namespace DVE\EntityORM\EntityManager;

abstract class EntityRepository
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return EntityRepository
     */
    public function setTableName(string $tableName): EntityRepository
    {
        $this->tableName = $tableName;
        return $this;
    }
}