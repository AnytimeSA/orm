<?php

namespace DVE\EntityORM\QueryBuilder;

interface DeleteQueryInterface
{
    /**
     * @return int Affected rows
     */
    public function execute(): int;
}