<?php

namespace Anytime\ORM\QueryBuilder;

interface DeleteQueryInterface
{
    /**
     * @return int Affected rows
     */
    public function execute(): int;
}
