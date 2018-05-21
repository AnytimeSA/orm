<?php

namespace DVE\EntityORM\EntityManager;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;
use DVE\EntityORM\QueryBuilder\MySqlQueryBuilder;
use DVE\EntityORM\QueryBuilder\QueryBuilderAbstract;

abstract class EntityManager
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    protected $snakeToCamelCaseStringConverter;

    /**
     * EntityManager constructor.
     * @param \PDO $pdo
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     */
    public function __construct(\PDO $pdo, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter)
    {
        $this->pdo = $pdo;
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
    }

    /**
     * @param Entity|Entity[] $entities
     */
    public function insert($entities)
    {
        if(is_object($entities)) {
            $entities = [$entities];
        }

        foreach($entities as $entity) {
            $pkeyValues = $entity->extractPrimaryKeyValues();
            $entityClass = get_class($entity);
            $isComposite = count($pkeyValues) > 1;

            // Composite primary keys should not be null for insert
            if($isComposite && in_array(null, $pkeyValues)) {
                throw new \RuntimeException('Composite primary key values should\'nt be null.');
            }

            $queryBuilder = new MySqlQueryBuilder($this->pdo, $this->snakeToCamelCaseStringConverter);
            $query = $queryBuilder
                ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_INSERT)
                ->setEntityClass($entityClass)
                ->getInsertQuery($entity)
            ;

            $insertId = $query->execute();

            // If not a composite pkey we update the pkey value with the last insert ID
            if(!$isComposite) {
                if(count($entityClass::PRIMARY_KEYS) > 0) {
                    $pkeyName = $entityClass::PRIMARY_KEYS[0];
                    $setter = 'set'.$this->snakeToCamelCaseStringConverter->convert($pkeyName);
                    $entity->$setter($insertId);
                }
            }
        }
    }


}