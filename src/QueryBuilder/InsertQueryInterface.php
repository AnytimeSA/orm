<?php

namespace DVE\EntityORM\QueryBuilder;

interface InsertQueryInterface
{
    /**
     * @return int Last insert ID
     */
    public function execute(): int;
}