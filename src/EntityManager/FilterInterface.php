<?php

namespace Anytime\ORM\EntityManager;


interface FilterInterface
{
    /**
     * @param $inputValue
     * @return mixed
     */
    public function apply($inputValue, string $entityClass, string $propertyName, array &$resultRow);
}
