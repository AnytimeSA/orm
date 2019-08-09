<?php

namespace Anytime\ORM\QueryBuilder\Expression;

class Expr implements ExpressionInterface
{
    /**
     * @var string
     */
    protected $expr;

    /**
     * @param string $expr SQL expression. It can contains the string "%FIELD%". This string will be replaced wih the real fieldname by the query builder when building the SQL string.
     *                     Example 1 : $someQueryBuilderProxy->setCountConnections('%FIELD% + 1')
     *                     Example 2 : $someQueryBuilderProxy->setFirstname('UPPERCASE(%FIELD%)')
     */
    public function __construct(string $expr)
    {
        $this->expr = $expr;
    }

    /**
     * @inheritDoc
     */
    public function getExpr(string $fieldName, string $fieldDelimiter = null): string
    {
        return str_replace('%FIELD%', (string)$fieldDelimiter . $fieldName . (string)$fieldDelimiter, $this->expr);
    }

}
