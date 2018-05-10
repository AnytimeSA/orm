<?php

namespace DVE\EntityORM\Converter;

interface StringConverterInterface
{
    /**
     * @param string $snakeCaseString
     * @return string
     */
    public function convert(string $snakeCaseString): string;
}