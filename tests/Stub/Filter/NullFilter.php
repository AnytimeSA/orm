<?php

namespace Anytime\ORM\Tests\Stub\Filter;

use Anytime\ORM\EntityManager\Filter;

class NullFilter extends Filter
{
    /**
     * @inheritDoc
     */
    public function apply($inputValue, string $entityClass, string $propertyName, array &$resultRow)
    {
        return null;
    }
}
