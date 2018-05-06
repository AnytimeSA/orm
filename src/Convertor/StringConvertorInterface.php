<?php

namespace DVE\EntityORM\Convertor;

interface StringConvertorInterface
{
    /**
     * @param string $snakeCaseString
     * @return string
     */
    public function convert(string $snakeCaseString): string;
}