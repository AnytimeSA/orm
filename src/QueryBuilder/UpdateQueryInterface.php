<?php

namespace Anytime\ORM\QueryBuilder;

interface UpdateQueryInterface
{
    /**
     * @return int Affected rows
     */
    public function execute(): int;
}
