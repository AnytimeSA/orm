<?php

namespace DVE\EntityORM\QueryBuilder;

class SelectQuery extends QueryAbstract implements SelectQueryInterface
{
    const FETCH_DATA_FORMAT_ENTITY = 'entity';
    const FETCH_DATA_FORMAT_ARRAY = 'array';

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var bool
     */
    private $fetchDone = true;

    /**
     * @var string
     */
    private $fetchDataFormat = self::FETCH_DATA_FORMAT_ENTITY;

    /**
     * @param string $entityClass
     * @return SelectQuery
     */
    public function setEntityClass(string $entityClass): SelectQuery
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @param string $fetchDataFormat
     * @return SelectQuery
     */
    public function setFetchDataFormat(string $fetchDataFormat): SelectQuery
    {
        $this->fetchDataFormat = $fetchDataFormat;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function fetchOne()
    {
        $result = $this->fetch();
        $this->fetchDone = true;
        $this->PDOStatement->closeCursor();
        return $result;
    }

    /**
     * @return mixed|null
     */
    public function fetch()
    {
        if($this->fetchDone) {
            $this->PDOStatement->execute($this->parameters);
            $this->fetchDone = false;
        }

        if($fetchedData = $this->PDOStatement->fetch(\PDO::FETCH_ASSOC)) {
            if($this->entityClass && $this->fetchDataFormat === self::FETCH_DATA_FORMAT_ENTITY) {
                return new $this->entityClass($fetchedData);
            } else {
                return $fetchedData;
            }
        } else {
            $this->fetchDone = true;
            $this->PDOStatement->closeCursor();
            return null;
        }
    }

    /**
     * @return array
     */
    public function fetchAll(): array
    {
        $results = [];

        $this->PDOStatement->execute($this->parameters);
        $fetchedData = $this->PDOStatement->fetchAll(\PDO::FETCH_ASSOC);

        if($this->entityClass && $this->fetchDataFormat === self::FETCH_DATA_FORMAT_ENTITY) {
            foreach($fetchedData as $result) {
                $entity = new $this->entityClass($result);
                $results[] = $entity;
            }
            $this->PDOStatement->closeCursor();
            return $results;
        } else {
            $this->PDOStatement->closeCursor();
            return $fetchedData;
        }
    }

    /**
     * @return mixed
     */
    public function fetchSingleScalarResult()
    {
        $this->PDOStatement->execute($this->parameters);
        $fetchedData = $this->PDOStatement->fetch(\PDO::FETCH_NUM);
        $this->PDOStatement->closeCursor();

        if($fetchedData && isset($fetchedData[0])) {
            return $fetchedData[0];
        }

        return null;
    }
}