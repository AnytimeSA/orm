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
     *                                     [tableName] => example_table
     *                                     [fieldName] => id
     *                                     [type] => int
     *                                     [allowNull] =>
     *                                     [keyType] =>
     *                                     [defaultValue] =>
     *                                     [dateFormat] =>
     *                                 )
     *
     *                         )
     *
     *                     [some_field] => Array
     *                         (
     *                             [0] => Array
     *                                 (
     *                                     [tableName] => example_table
     *                                     [fieldName] => some_field
     *                                     [type] => string
     *                                     [allowNull] =>
     *                                     [keyType] =>
     *                                     [defaultValue] =>
     *                                     [dateFormat] =>
     *                                 )
     *
     *                         )
     *
     *                     [some_date_field] => Array
     *                         (
     *                             [0] => Array
     *                                 (
     *                                     [tableName] => example_table
     *                                     [fieldName] => some_date_field
     *                                     [type] => string
     *                                     [allowNull] =>
     *                                     [keyType] =>
     *                                     [defaultValue] =>
     *                                     [dateFormat] => Y-m-d H:i:s
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
     *                                     [tableName] => example_table
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


