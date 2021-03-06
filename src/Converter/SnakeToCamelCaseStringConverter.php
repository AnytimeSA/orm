<?php

namespace Anytime\ORM\Converter;

use Nayjest\StrCaseConverter\Str;

class SnakeToCamelCaseStringConverter implements StringConverterInterface
{
    public function convert(string $snakeCaseString): string
    {
        return Str::toCamelCase($snakeCaseString);
    }
}
