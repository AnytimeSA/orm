<?php

namespace Anytime\ORM\EntityManager;

class FilterCollection
{
    /**
     * @var Filter[]
     */
    private $filters = [];

    /**
     * @param callable $fn
     * @return FilterCollection
     */
    public function addFilter(Filter $filter)
    {
        foreach($this->filters as $existingFilter) {
            if($existingFilter->getName() === $filter->getName()) {
                throw new \InvalidArgumentException('A filter with the same name already exists!');
            }
        }

        $this->filters[] = $filter;
        return $this;
    }

    /**
     * @param array $resultRow
     * @param string $entityClass
     * @return array
     */
    public function applyFilters(array $resultRow, string $entityClass): array
    {
        foreach($this->filters as $filter) {

            $scope = $filter->getScope();
            $isGlobal = is_null($scope);

            if($isGlobal) {
                foreach($resultRow as $field => $value) {
                    $resultRow[$field] = $filter->apply($value, $entityClass, $field, $resultRow);
                }
            } elseif(array_key_exists($entityClass, $scope)) {
                foreach($resultRow as $field => $value) {
                    foreach($scope[$entityClass] as $regexp) {
                        if(preg_match('/'.$regexp.'/', $field)) {
                            $resultRow[$field] = $filter->apply($value, $entityClass, $field, $resultRow);
                        }
                    }
                }
            }
        }
        return $resultRow;
    }
}
