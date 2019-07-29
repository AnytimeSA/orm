<?php

namespace Anytime\ORM\EntityManager;

abstract class Filter implements FilterInterface
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string|null
     */
    protected $scope = null;

    /**
     * Filter constructor.
     * @param string $name
     * @param string[]|null $scope [entityClass => propertyRegexp] if null the filter will be global to all entities and properties
     */
    public function __construct(string $name, $scope = null)
    {
        $this->scope = $scope;
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
