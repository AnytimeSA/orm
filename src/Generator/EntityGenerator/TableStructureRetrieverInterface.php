<?php

namespace Anytime\ORM\Generator\EntityGenerator;

interface TableStructureRetrieverInterface
{
    /**
     * The return array should have the following structure :
     *
     * Array
     * (
     *     [example_table] => Array
     *         (
     *             [structure] => Array
     *                 (
     *                     [id] => Array
     *                         (
     *                             [0] => Array
     *                                 (
     *                                     [fieldName] => id
     *                                     [type] => int
     *                                     [allowNull] =>
     *                                     [keyType] => PRI
     *                                     [defaultValue] =>
     *                                 )
     *
     *                         )
     *
     *                     [some_field] => Array
     *                         (
     *                             [0] => Array
     *                                 (
     *                                     [fieldName] => some_field
     *                                     [type] => string
     *                                     [allowNull] =>
     *                                     [keyType] => MUL
     *                                     [defaultValue] =>
     *                                 )
     *
     *                         )
     *                     ...
     *
     *                 )
     *
     *             [indexes] => Array
     *                 (
     *                     [index_name_example_1] => Array
     *                         (
     *                             [0] => Array
     *                                 (
     *                                     [indexName] => index_name_example_1
     *                                     [columnName] => some_field
     *                                     [allowNull] =>
     *                                 )
     *
     *                         )
     *
     *                     ...
     *                 )
     *
     *         )
     *
     * )
     *
     * @param array|null $tableList
     * @return array
     */
    public function retrieve(array $tableList = []): array;
}


