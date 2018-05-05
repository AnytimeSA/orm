<?php

namespace DVE\EntityORM\EntityManager;

abstract class Entity
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}