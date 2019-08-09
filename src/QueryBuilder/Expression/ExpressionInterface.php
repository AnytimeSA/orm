<?php

namespace Anytime\ORM\QueryBuilder\Expression;

interface ExpressionInterface
{
    /**
     * @param string $fieldName
     * @param string|null $fieldDelimiter
     * @return string A string containing the SQL expression
     */
    public function getExpr(string $fieldName, string $fieldDelimiter = null): string;
}
