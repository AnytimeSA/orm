<?php

namespace Anytime\ORM\Tests\QueryBuilder\Expression;

use Anytime\ORM\QueryBuilder\Expression\Expr;
use Anytime\ORM\Tests\ORMTestCase;

class ExprTest extends ORMTestCase
{
    /**
     * @param string $expected
     * @param string $exprStr
     * @param string $fieldName
     * @param string|null $delim
     * @dataProvider getSuccessValues
     */
    public function testExpression(string $expected, string $exprStr, string $fieldName, $delim = null)
    {
        $expr = new Expr($exprStr);
        $this->assertSame($expected, $expr->getExpr($fieldName, $delim));
    }

    /**
     * @return array
     */
    public function getSuccessValues()
    {
        // Expected, Expression string, field name, delimiter char
        return [
            ['1', '1', 'some_field', null],
            ['1', '1', 'some_field', null],
            ['UPPER(some_field)', 'UPPER(%FIELD%)', 'some_field', null],
            ['UPPER(`some_field`)', 'UPPER(%FIELD%)', 'some_field', '`'],
            ['CONCAT(`some_field`, `some_field`, `some_field`)', 'CONCAT(%FIELD%, %FIELD%, %FIELD%)', 'some_field', '`'],
        ];
    }

}
