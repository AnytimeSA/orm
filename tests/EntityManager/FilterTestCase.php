<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\Filter;
use PHPUnit\Framework\TestCase;

class FilterTestCase extends TestCase
{
    /**
     * @param string $name
     * @param array|null $scope
     * @return Filter
     */
    protected function getFilter(string $name, array $scope = null)
    {
        return new class($name, $scope) extends Filter {
            public function apply($inputValue, string $entityClass, string $propertyName, array &$resultRow)
            {
                return 'value-was-filtered'; // We fake the transformed value
            }
        };
    }
}
