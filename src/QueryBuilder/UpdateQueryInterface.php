<?php

namespace DVE\EntityORM\QueryBuilder;

interface UpdateQueryInterface
{
    /**
     * @return int Affected rows
     */
    public function execute(): int;
}
