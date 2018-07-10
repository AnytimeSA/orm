<?php

namespace Anytime\ORM\QueryBuilder;

interface InsertQueryInterface
{
    /**
     * @return int Last insert ID
     */
    public function execute(): int;
}
