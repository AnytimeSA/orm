<?php

namespace Anytime\ORM\QueryBuilder\Expression;

interface ExpressionInterface
{
    /**
     * @param string $fieldName
     * @return string A string containing the SQL expression
     */
    public function getExpr(string $fieldName): string;
}
