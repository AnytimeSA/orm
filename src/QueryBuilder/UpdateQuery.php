<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\FilterCollection;

class UpdateQuery extends QueryAbstract implements UpdateQueryInterface
{
    /**
     * UpdateQuery constructor.
     * @param Connection $connection
     * @param \PDOStatement $PDOStatement
     * @param FilterCollection $filterCollection
     * @param $parameters
     * @param array $fieldsToUpdate
     */
    public function __construct(Connection $connection, \PDOStatement $PDOStatement, FilterCollection $filterCollection, $parameters, array $fieldsToUpdate = [])
    {
        $newFieldsToUpdate = [];

        // This is done to avoid parameters name conflict with the parameters of the where clause
        foreach($fieldsToUpdate as $fieldName => $value) {
            $newFieldsToUpdate['UPDATE_VALUE_'.$fieldName] = $value;
        }

        unset($fieldsToUpdate);

        parent::__construct($connection, $PDOStatement, $filterCollection, $parameters + $newFieldsToUpdate);
    }

    /**
     * @inheritdoc
     */
    public function execute(): int
    {
        $this->PDOStatement->execute($this->parameters);
        $this->throwPdoError($this->PDOStatement);
        return $this->PDOStatement->rowCount();
    }
}
