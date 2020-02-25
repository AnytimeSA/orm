<?php

namespace Anytime\ORM\Converter;

interface StringConverterInterface
{
    /**
     * @param string $string
     * @return string
     */
    public function convert(string $string): string;
}
