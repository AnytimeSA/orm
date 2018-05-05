<?php

namespace DVE\EntityORM\EntityManager;

abstract class Entity
{
    protected $tableName;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     * @return Entity
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }


}