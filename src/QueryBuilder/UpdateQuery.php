<?php

namespace DVE\EntityORM\QueryBuilder;

class UpdateQuery extends QueryAbstract implements UpdateQueryInterface
{
    /**
     * UpdateQuery constructor.
     * @param \PDO $pdo
     * @param \PDOStatement $PDOStatement
     * @param $parameters
     * @param array $fieldsToUpdate
     */
    public function __construct(\PDO $pdo, \PDOStatement $PDOStatement, $parameters, array $fieldsToUpdate = [])
    {
        $newFieldsToUpdate = [];

        // This is done to avoid parameters name conflict with the parameters of the where clause
        foreach($fieldsToUpdate as $fieldName => $value) {
            $newFieldsToUpdate['UPDATE_VALUE_'.$fieldName] = $value;
        }

        unset($fieldsToUpdate);

        parent::__construct($pdo, $PDOStatement, $parameters + $newFieldsToUpdate);
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
