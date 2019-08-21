<?php

namespace Anytime\ORM\Generator\QueryBuilderProxyGenerator;

use Anytime\ORM\Generator\QueryBuilderProxyGenerator\QueryBuilderProxyGeneratorInterface;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Symfony\Component\Filesystem\Filesystem;
use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\Generator\EntityGenerator\TableStructureRetrieverInterface;

class QueryBuilderProxyGenerator implements QueryBuilderProxyGeneratorInterface
{
    /**
     * @var SnakeToCamelCaseStringConverter
     */
    private $snakeToCamelCaseStringConverter;

    /**
     * @var TableStructureRetrieverInterface
     */
    private $tableStructureRetriever;

    /**
     * @var string
     */
    private $databaseType;

    /**
     * @var string
     */
    private $queryBuilderProxyDirectory;

    /**
     * @var string
     */
    private $queryBuilderProxyNamespace;

    /**
     * EntityGenerator constructor.
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param TableStructureRetrieverInterface $tableStructureRetriever
     * @param string $queryBuilderDirectory
     * @param string|null $queryBuilderNamespace
     */
    public function __construct(
        SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter,
        TableStructureRetrieverInterface $tableStructureRetriever,
        string $databaseType,
        string $queryBuilderProxyDirectory,
        string $queryBuilderProxyNamespace = null)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->tableStructureRetriever = $tableStructureRetriever;
        $this
            ->setQueryBuilderProxyDirectory($queryBuilderProxyDirectory)
            ->setQueryBuilderProxyNamespace($queryBuilderProxyNamespace)
            ->setDatabaseType($databaseType)
        ;
    }

    /**
     * @param string $queryBuilderDirectory
     * @return QueryBuilderProxyGeneratorInterface
     */
    public function setQueryBuilderProxyDirectory(string $queryBuilderProxyDirectory): QueryBuilderProxyGeneratorInterface
    {
        $this->queryBuilderProxyDirectory = $queryBuilderProxyDirectory;
        return $this;
    }

    /**
     * @param string $databaseType
     * @return QueryBuilderProxyGeneratorInterface
     */
    public function setDatabaseType(string $databaseType): QueryBuilderProxyGeneratorInterface
    {
        $this->databaseType = $databaseType;
        return $this;
    }

    /**
     * @param string $queryBuilderNamespace
     * @return QueryBuilderProxyGeneratorInterface
     */
    public function setQueryBuilderProxyNamespace(string $queryBuilderProxyNamespace): QueryBuilderProxyGeneratorInterface
    {
        $queryBuilderProxyNamespace = trim($queryBuilderProxyNamespace, '\\');

        if(preg_match('/^([a-z0-9_\\\]+)$/i', $queryBuilderProxyNamespace)) {
            $this->queryBuilderProxyNamespace = $queryBuilderProxyNamespace;
        } else {
            throw new \RuntimeException('Invalid query builder proxy namespace please use a correct namespace format. Example: My\\Namespace.');
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generate(array $tableList = [], array $ignoredTables = [])
    {
        $fileSystem = new Filesystem();
        $fileSystem->mkdir($this->queryBuilderProxyDirectory);

        foreach($this->tableStructureRetriever->retrieve($tableList) as $tableName => $tableStruct) {
            if (in_array($tableName, $ignoredTables)) {
                continue;
            }

            // UPDATE
            $queryBuilderUpdateProxyClassName = ucfirst($this->snakeToCamelCaseStringConverter->convert($tableName) . 'QueryBuilderUpdateProxy');
            $queryBuilderUpdateProxyGeneratedSourceCode = $this->generateQueryBuilderUpdateProxyClassString($tableName, $tableStruct);
            file_put_contents($this->queryBuilderProxyDirectory . '/' . $queryBuilderUpdateProxyClassName . '.php', $queryBuilderUpdateProxyGeneratedSourceCode);

            // INSERT
            // ...

            // DELETE
            // ...
        }
    }

    /**
     * @inheritDoc
     */
    public function generateQueryBuilderUpdateProxyClassString(string $tableName, array $tableStruct): string
    {
        $entityName = $this->snakeToCamelCaseStringConverter->convert($tableName);
        $queryBuilderName = $entityName."QueryBuilderUpdateProxy";

        // PHP Tag
        $classString = '<?php'."\n\n";

        // Namespace
        $classString .= "namespace ". $this->queryBuilderProxyNamespace . ";\n\n";

        // Uses
        $classString .= "use Anytime\\ORM\\QueryBuilder\\QueryBuilderAbstract;\n";
        $classString .= "use Anytime\\ORM\\QueryBuilder\\UpdateQuery;\n";
        $classString .= "use Anytime\\ORM\\QueryBuilder\\QueryBuilderProxyInterface;\n";

        $classString .= "\n";

        // Class
        $classString .= "class $queryBuilderName implements QueryBuilderProxyInterface\n";
        $classString .= "{\n";

        $classString .= "    /**\n";
        $classString .= "     * @var QueryBuilderAbstract\n";
        $classString .= "     */\n";
        $classString .= "    private \$queryBuilder;\n\n";

        $classString .= "    public function __construct(QueryBuilderAbstract \$queryBuilder)\n";
        $classString .= "    {\n";
        $classString .= "        \$this->queryBuilder = \$queryBuilder;\n";
        $classString .= "    }\n\n";


        $classString .= "    public function getQueryBuilder(): QueryBuilderAbstract\n";
        $classString .= "    {\n";
        $classString .= "        return \$this->queryBuilder;\n";
        $classString .= "    }\n\n";


        $classString .= "    /**\n";
        $classString .= "     * @return UpdateQuery\n";
        $classString .= "     */\n";
        $classString .= "    public function getQuery()\n";
        $classString .= "    {\n";
        $classString .= "        return \$this->queryBuilder->getUpdateQuery();\n";
        $classString .= "    }\n\n";

        $classString .= "    public function execute(): int\n";
        $classString .= "    {\n";
        $classString .= "        return \$this->getQuery()->execute();\n";
        $classString .= "    }\n\n";

        // Update methods list
        foreach($tableStruct['structure'] as $fieldStruct) {
            $fieldName = array_key_exists('fieldName', $fieldStruct) ? $fieldStruct['fieldName'] : null;
            $fieldType = array_key_exists('type', $fieldStruct) ? $fieldStruct['type'] : null;
            $nullable = array_key_exists('allowNull', $fieldStruct) ? $fieldStruct['allowNull'] : false;
            $isPrimary = array_key_exists('keyType', $fieldStruct) && $fieldStruct['keyType'] === 'PRI' ? true : false;
            $dateFormat = array_key_exists('dateFormat', $fieldStruct) ? $fieldStruct['dateFormat'] : 'Y-m-d H:i:s';
            $setMethodName = 'set' . $this->snakeToCamelCaseStringConverter->convert($fieldName);

            // We do not set the Primary Key value
            if($isPrimary) {
                continue;
            }

            $phpType = ($fieldType === 'date' ? '\DateTime' : $fieldType);
            $typeHintPhpDoc = ($nullable ? 'null|' . $phpType : $phpType);

            $classString .= "    /**\n";
            $classString .= "     * @param $typeHintPhpDoc \$value\n";
            $classString .= "     * @return $queryBuilderName\n";
            $classString .= "     */\n";
            $classString .= "    public function $setMethodName(\$value): $queryBuilderName\n";
            $classString .= "    {\n";

            if($fieldType === 'date') {
                $classString .= "        \$this->queryBuilder->addFieldToUpdate('$fieldName', \$value->format('$dateFormat'));\n";
            } else {
                $classString .= "        \$this->queryBuilder->addFieldToUpdate('$fieldName', \$value);\n";
            }

            $classString .= "        return \$this;\n";
            $classString .= "    }\n\n";
        }


        // END-Class
        $classString .= "}";

        return $classString;
    }
}
