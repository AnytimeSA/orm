<?php

namespace Anytime\ORM\Tests\Converter;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use PHPUnit\Framework\TestCase;

class SnakeToCamelCaseStringConverterTest extends TestCase
{
    /**
     * @group Converter
     * @group SnakeToCamelCaseStringConverter
     * @dataProvider getDataSet
     * @param string $snakeCaseString
     * @param string $expectedConvertedString
     */
    public function testConvertMethod(string $snakeCaseString, string $expectedConvertedString)
    {
        $converter = new SnakeToCamelCaseStringConverter();
        $this->assertSame($expectedConvertedString, $converter->convert($snakeCaseString));
    }

    /**
     * @return string[]
     */
    public function getDataSet()
    {
        return [
            ['SOMESTRING', 'SOMESTRING'],
            ['Some_string', 'SomeString'],
            ['SomeString', 'SomeString'],
            ['some_string', 'SomeString'],
            ['SOME_STRING', 'SOMESTRING'],
            ['_some_string', 'SomeString'],
            ['_SOME_STRING_', 'SOMESTRING'],
            ['_some_string', 'SomeString']
        ];
    }
}
