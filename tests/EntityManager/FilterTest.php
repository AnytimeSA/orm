<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\Filter;

class FilterTest extends FilterTestCase
{
    /**
     * @group Filters
     */
    public function testInstantiateFilterWithNameAndNoScope()
    {
        $results = []; // We dont care for this test
        $filter = $this->getFilter('Some filter');
        $this->assertNull($filter->getScope());
        $this->assertSame('Some filter', $filter->getName());

        $newValue = $filter->apply('1234', 'Some\Entity\Class', 'prop_name', $results);

        $this->assertSame('value-was-filtered', $newValue);
    }

    /**
     * @group Filters
     */
    public function testInstantiateFilterWithNameAndScope()
    {
        $results = []; // We dont care for this test
        $scope = [
            'Some\Entity\Class' => [
                '^some_match_property$',
                'date'
            ]
        ];
        $filter = $this->getFilter('Some filter', $scope);
        $this->assertSame('Some filter', $filter->getName());
        $this->assertSame($scope, $filter->getScope());
    }


}
