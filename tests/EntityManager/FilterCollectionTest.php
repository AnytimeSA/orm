<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\FilterCollection;

class FilterCollectionTest extends FilterTestCase
{
    /**
     * @group Filters
     */
    public function testAddingSameNameFilterFail()
    {
        $filter1 = $this->getFilter('Some filter');
        $filter2 = $this->getFilter('Some filter');
        $filterCollection = new FilterCollection();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A filter with the same name already exists!');

        $filterCollection->addFilter($filter1)->addFilter($filter2);
    }

    /**
     * @group Filters
     */
    public function testApplyFiltersWithGlobalSccope()
    {
        $filter = $this->getFilter('Some filter');
        $filterCollection = new FilterCollection();
        $filterCollection->addFilter($filter);

        $resultRow = [
            'random'        => 'aaa',
            'field'         => 'bbb',
            'name'          => 'ccc'
        ];

        $this->assertSame(
            [
                'random'        => 'value-was-filtered',
                'field'         => 'value-was-filtered',
                'name'          => 'value-was-filtered'
            ],
            $filterCollection->applyFilters($resultRow, 'Some\Entity\Class')
        );
    }

    /**
     * @group Filters
     */
    public function testApplyFiltersWithSpecificScope()
    {
        $scope = [
            'Some\Entity\Class' => [
                '^some_match_property$',
                'date',
                '^start_with_(.*)'
            ]
        ];

        $filter = $this->getFilter('Some filter', $scope);

        $filterCollection = new FilterCollection();
        $filterCollection->addFilter($filter);

        $resultRow = [
            'some_date_field'       => '2012-01-01 01:01:01',
            'non_matched_field'     => 'abcd',
            'date_create'           => '2012-02-02 02:02:02',
            'some_match_property'   => 'abcd',
            'start_with_1'          => 'abcd',
            '_start_with_1'         => 'abcd'
        ];

        $this->assertSame(
            [
                'some_date_field'       => 'value-was-filtered',
                'non_matched_field'     => 'abcd',
                'date_create'           => 'value-was-filtered',
                'some_match_property'  =>  'value-was-filtered',
                'start_with_1'          => 'value-was-filtered',
                '_start_with_1'         => 'abcd'
            ],
            $filterCollection->applyFilters($resultRow, 'Some\Entity\Class')
        );
    }
}
