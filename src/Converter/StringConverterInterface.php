<?php

namespace Anytime\ORM\Converter;

interface StringConverterInterface
{
    /**
     * @param string $snakeCaseString
     * @return string
     */
    public function convert(string $snakeCaseString): string;
}
