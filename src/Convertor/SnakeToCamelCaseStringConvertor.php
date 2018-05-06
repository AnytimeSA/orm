<?php

namespace DVE\EntityORM\Convertor;

use Nayjest\StrCaseConverter\Str;

class SnakeToCamelCaseStringConvertor implements StringConvertorInterface
{
    public function convert(string $snakeCaseString): string
    {
        return Str::toCamelCase($snakeCaseString);
    }
}