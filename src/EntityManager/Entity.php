<?php

namespace Anytime\ORM\EntityManager;

abstract class Entity
{
    /**
     * This constant must be overriden by the concrete entity class constant.
     * It contains the table name associated with the entity.
     */
    const TABLENAME = '';

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
     * @var array
     */
    protected $dataSetterUsed = [];

    /**
     * Entity constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data + $this->data;
    }

    /**
     * @return array
     */
    public function extractPrimaryKeyValues()
    {
        $pkeyValues = [];

        foreach(static::PRIMARY_KEYS as $pkey) {
            $pkeyValues[$pkey] = $this->data[$pkey];
        }

        return $pkeyValues;
    }

    /**
     * @return array
     */
    public function extractData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function extractSetterUsedData(): array
    {
        $returnData = [];
        foreach($this->data as $fieldName => $value) {
            if (isset($this->dataSetterUsed[$fieldName]) && $this->dataSetterUsed[$fieldName]) {
                $returnData[$fieldName] = $value;
            }
        }
        return $returnData;
    }

    /**
     * @return bool
     */
    public function updateNeeded()
    {
        return in_array(true, $this->dataSetterUsed);
    }

    /**
     * @param string|null $fieldName
     */
    public function resetDataSetterUsed(string $fieldName = null)
    {
        if($fieldName) {
            if(array_key_exists($fieldName, $this->dataSetterUsed)) {
                $this->dataSetterUsed[$fieldName] = false;
            }
        } else {
            foreach($this->dataSetterUsed as $fieldName => $value) {
                $this->dataSetterUsed[$fieldName] = false;
            }
        }
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