<?php

namespace DVE\EntityORM\EntityManager;

abstract class Entity
{
    /**
     * This property should must be overridden by the generated chield class
     *
     * @var string
     */
    public $tableName;

    /**
     * This property is used to cache object like DateTime generated when the getter is used. LIke this the DateTime object with the value is generated only one time.
     * @var array
     */
    protected $cachedReturnedObject = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Entity constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data + $this->data;
    }

    /**
     * @param string $method
     * @param string $dateTimeString
     * @return mixed
     */
    protected function convertDateTimeStringToObject(string $method, string $dateTimeString)
    {
        if(!array_key_exists($method, $this->cachedReturnedObject)) {
            $this->cachedReturnedObject[$method] = new \DateTime($dateTimeString);
        }
        return $this->cachedReturnedObject[$method];
    }


}