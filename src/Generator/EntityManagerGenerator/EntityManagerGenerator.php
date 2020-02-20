<?php

namespace Anytime\ORM\Generator\EntityManagerGenerator;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\Generator\EntityGenerator\TableStructureRetrieverInterface;
use Symfony\Component\Filesystem\Filesystem;

class EntityManagerGenerator implements EntityManagerGeneratorInterface
{
    /**
     * @var string
     */
    private $entityManagerDirectory;

    /**
     * @var string
     */
    private $entityManagerNamespace;

    /**
     * @var string
     */
    private $userEntityRepositoryDirectory;

    /**
     * @var string
     */
    private $userEntityRepositoryNamespace;

    /**
     * @var string
     */
    private $userManagerDirectory;

    /**
     * @var string
     */
    private $userManagerNamespace;

    /**
     * @var string
     */
    private $entityNamespace;

    /**
     * @var string
     */
    private $queryBuilderProxyDirectory;

    /**
     * @var string
     */
    private $queryBuilderProxyNamespace;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    private $snakeToCamelCaseStringConverter;

    /**
     * @var TableStructureRetrieverInterface
     */
    private $tableStructureRetriever;

    /**
     * EntityManagerGenerator constructor.
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param TableStructureRetrieverInterface $tableStructureRetriever
     * @param string $entityManagerDirectory
     * @param string|null $entityManagerNamespace
     * @param string $userEntityRepositoryDirectory
     * @param string $userEntityRepositoryNamespace
     * @param string $userManagerDirectory
     * @param string $userManagerNamespace
     * @param string $entityNamespace
     */
    public function __construct(
        SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter,
        TableStructureRetrieverInterface $tableStructureRetriever,
        string $entityManagerDirectory,
        string $entityManagerNamespace,
        string $userEntityRepositoryDirectory,
        string $userEntityRepositoryNamespace,
        string $userManagerDirectory,
        string $userManagerNamespace,
        string $entityNamespace,
        string $queryBuilderProxyDirectory,
        string $queryBuilderProxyNamespace
    )
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->tableStructureRetriever = $tableStructureRetriever;
        $this
            ->setEntityManagerDirectory($entityManagerDirectory)
            ->setEntityManagerNamespace($entityManagerNamespace)
            ->setUserEntityRepositoryDirectory($userEntityRepositoryDirectory)
            ->setUserEntityRepositoryNamespace($userEntityRepositoryNamespace)
            ->setUserManagerDirectory($userManagerDirectory)
            ->setUserManagerNamespace($userManagerNamespace)
            ->setEntityNamespace($entityNamespace)
            ->setQueryBuilderProxyDirectory($queryBuilderProxyDirectory)
            ->setQueryBuilderProxyNamespace($queryBuilderProxyNamespace)
        ;
    }

    /**
     * @param string $entityManagerDirectory
     * @return EntityManagerGenerator
     */
    public function setEntityManagerDirectory(string $entityManagerDirectory): EntityManagerGenerator
    {
        $this->entityManagerDirectory = $entityManagerDirectory;

        return $this;
    }

    /**
     * @param string $entityManagerNamespace
     * @return EntityManagerGenerator
     */
    public function setEntityManagerNamespace(string $entityManagerNamespace): EntityManagerGenerator
    {
        $entityManagerNamespace = trim($entityManagerNamespace, '\\');

        if(preg_match('/^([a-z0-9_\\\]+)$/i', $entityManagerNamespace)) {
            $this->entityManagerNamespace = $entityManagerNamespace;
        } else {
            throw new \RuntimeException('Invalid entity manager namespace please use a correct namespace format. Example: My\\Namespace.');
        }
        return $this;
    }


    /**
     * Define the directory where users will create the concrete EntityRepository classes extending EntityRepository
     *
     * @param string $userEntityRepositoryDirectory
     * @return EntityManagerGenerator
     */
    public function setUserEntityRepositoryDirectory(string $userEntityRepositoryDirectory): EntityManagerGenerator
    {
        $this->userEntityRepositoryDirectory = $userEntityRepositoryDirectory;

        return $this;
    }

    /**
     * @param string $userEntityRepositoryNamespace
     * @return EntityManagerGenerator
     */
    public function setUserEntityRepositoryNamespace(string $userEntityRepositoryNamespace): EntityManagerGenerator
    {
        $userEntityRepositoryNamespace = trim($userEntityRepositoryNamespace, '\\');

        if(preg_match('/^([a-z0-9_\\\]+)$/i', $userEntityRepositoryNamespace)) {
            $this->userEntityRepositoryNamespace = $userEntityRepositoryNamespace;
        } else {
            throw new \RuntimeException('Invalid user entity repository namespace please use a correct namespace format. Example: My\\Namespace.');
        }
        return $this;
    }

    /**
     * Define the directory where users will create the concrete Manager classes extending Manager
     *
     * @param string $userManagerDirectory
     * @return EntityManagerGenerator
     */
    public function setUserManagerDirectory(string $userManagerDirectory): EntityManagerGenerator
    {
        $this->userManagerDirectory = $userManagerDirectory;

        return $this;
    }

    /**
     * @param string $userManagerNamespace
     * @return EntityManagerGenerator
     */
    public function setUserManagerNamespace(string $userManagerNamespace): EntityManagerGenerator
    {
        $userManagerNamespace = trim($userManagerNamespace, '\\');

        if(preg_match('/^([a-z0-9_\\\]+)$/i', $userManagerNamespace)) {
            $this->userManagerNamespace = $userManagerNamespace;
        } else {
            throw new \RuntimeException('Invalid user manager namespace please use a correct namespace format. Example: My\\Namespace.');
        }
        return $this;
    }

    /**
     * @param string $entityNamespace
     * @return EntityManagerGenerator
     */
    public function setEntityNamespace(string $entityNamespace): EntityManagerGenerator
    {
        $this->entityNamespace = $entityNamespace;
        return $this;
    }

    /**
     * @param string $queryBuilderProxyDirectory
     * @return EntityManagerGenerator
     */
    public function setQueryBuilderProxyDirectory(string $queryBuilderProxyDirectory): EntityManagerGenerator
    {
        $this->queryBuilderProxyDirectory = $queryBuilderProxyDirectory;
        return $this;
    }

    /**
     * @param string $queryBuilderProxyNamespace
     * @return EntityManagerGenerator
     */
    public function setQueryBuilderProxyNamespace(string $queryBuilderProxyNamespace): EntityManagerGenerator
    {
        $this->queryBuilderProxyNamespace = $queryBuilderProxyNamespace;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generate(array $tableList = [], array $ignoredTables = [])
    {
        $fileSystem = new Filesystem();
        $fileSystem->mkdir($this->entityManagerDirectory);

        $tableStructList = $this->tableStructureRetriever->retrieve($tableList);

        // Repositories class
        $sourceCode = $this->generateDynamicRepositories($tableStructList);
        file_put_contents($this->entityManagerDirectory . '/DynamicRepositories.php', $sourceCode);

        // Managers class
        $sourceCode = $this->generateDynamicManagers($tableStructList);
        file_put_contents($this->entityManagerDirectory . '/DynamicManagers.php', $sourceCode);

        // EntityManager class
        $sourceCode = $this->generateDynamicEntityManager();
        file_put_contents($this->entityManagerDirectory . '/DynamicEntityManager.php', $sourceCode);

        // DefaultManager classes
        $managersDir = $this->entityManagerDirectory . '/DefaultManager';
        if(file_exists($managersDir) && is_dir($managersDir)) {
            foreach(glob($managersDir.'/*') as $file) {
                if(is_file($file)) {
                    unlink($file);
                }
            }
        } else {
            mkdir($managersDir);
        }
        foreach($tableStructList as $tableName => $tableStruct) {
            if (in_array($tableName, $ignoredTables)) {
                continue;
            }

            $className = $this->snakeToCamelCaseStringConverter->convert($tableName).'Manager';
            $sourceCode = $this->generateDynamicManager($tableStruct, $className, $tableName);
            file_put_contents($this->entityManagerDirectory . '/DefaultManager/'.$className.'.php', $sourceCode);
        }

        // DefaultRepository classes
        $repositoriesDir = $this->entityManagerDirectory . '/DefaultRepository';
        if(file_exists($repositoriesDir) && is_dir($repositoriesDir)) {
            foreach(glob($repositoriesDir.'/*') as $file) {
                if(is_file($file)) {
                    unlink($file);
                }
            }
        } else {
            mkdir($repositoriesDir);
        }
        foreach($tableStructList as $tableName => $tableStruct) {
            if (in_array($tableName, $ignoredTables)) {
                continue;
            }

            $className = $this->snakeToCamelCaseStringConverter->convert($tableName).'EntityRepository';
            $sourceCode = $this->generateDynamicRepository($tableStruct, $className);
            file_put_contents($this->entityManagerDirectory . '/DefaultRepository/'.$className.'.php', $sourceCode);
        }
    }

    /**
     * @return string
     */
    private function generateDynamicEntityManager()
    {
        $sourceCode = "<?php\n\n";

        // Namespace block
        if($this->entityManagerNamespace) {
            $sourceCode .= "namespace " . $this->entityManagerNamespace.";\n";
        }

        // Use block
        $sourceCode .= "\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\EntityManager;\n";
        $sourceCode .= "use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;\n";
        $sourceCode .= "use Anytime\ORM\QueryBuilder\QueryBuilderFactory;\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\Connection;\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\FilterCollection;\n";
        $sourceCode .= "\n";

        // Class block
        $sourceCode .= "class DynamicEntityManager extends EntityManager\n";
        $sourceCode .= "{\n";
        $sourceCode .= "    /**\n";
        $sourceCode .= "     * @var DynamicRepositories\n";
        $sourceCode .= "     */\n";
        $sourceCode .= "    public \$repositories;\n";
        $sourceCode .= "\n";
        $sourceCode .= "    /**\n";
        $sourceCode .= "     * @var DynamicManagers\n";
        $sourceCode .= "     */\n";
        $sourceCode .= "    public \$managers;\n";
        $sourceCode .= "\n";
        $sourceCode .= "    public function __construct(Connection \$connection, SnakeToCamelCaseStringConverter \$snakeToCamelCaseStringConverter, DynamicRepositories \$dynamicRepositories, DynamicManagers \$dynamicManagers, QueryBuilderFactory \$queryBuilderFactory, FilterCollection \$filterCollection, string \$databaseType)\n";
        $sourceCode .= "    {\n";
        $sourceCode .= "        \$this->repositories = \$dynamicRepositories;\n";
        $sourceCode .= "        \$this->managers = \$dynamicManagers;\n";
        $sourceCode .= "        \$this->managers = \$dynamicManagers;\n";
        $sourceCode .= "        parent::__construct(\$connection, \$snakeToCamelCaseStringConverter, \$queryBuilderFactory, \$filterCollection, \$databaseType);\n";
        $sourceCode .= "    }\n";
        $sourceCode .= "}\n\n";

        return $sourceCode;
    }

    /**
     * @param array $tableStructList
     * @return string
     */
    private function generateDynamicRepositories(array $tableStructList)
    {
        $sourceCode = "<?php\n\n";

        // Namespace block
        if($this->entityManagerNamespace) {
            $sourceCode .= "namespace " . $this->entityManagerNamespace.";\n";
        }

        // Use block
        $sourceCode .= "use Anytime\ORM\EntityManager\Repositories;\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\EntityRepository;\n";

        foreach($tableStructList as $tableName => $tableStruct) {
            $entityName = $this->snakeToCamelCaseStringConverter->convert($tableName);
            $repositoryClassName = $entityName.'EntityRepository';
            $repositoryFullClassName = $this->userEntityRepositoryNamespace . "\\" . $repositoryClassName;
            $sourceCode .= "use $repositoryFullClassName;\n";
        }

        // Class
        $sourceCode .= "\n";
        $sourceCode .= "class DynamicRepositories extends Repositories\n";
        $sourceCode .= "{\n";

        // Methods
        foreach($tableStructList as $tableName => $tableStruct) {
            $entityName = $this->snakeToCamelCaseStringConverter->convert($tableName);
            $repositoryClassName = $entityName.'EntityRepository';
            $repositoryGetterName = "get" . $repositoryClassName;
            $repositoryFullClassName = $this->userEntityRepositoryNamespace . "\\" . $repositoryClassName;
            $entityFullClassName = $this->entityNamespace . '\\' . $entityName;
            $defaultRepositoryFullClassName = $this->entityManagerNamespace . '\\DefaultRepository\\' . $repositoryClassName;

            $sourceCode .= "    /**\n";
            $sourceCode .= "     * @return $repositoryClassName|\\$defaultRepositoryFullClassName\n";
            $sourceCode .= "     */\n";
            $sourceCode .= "    public function $repositoryGetterName(): \\$defaultRepositoryFullClassName\n";
            $sourceCode .= "    {\n";
            $sourceCode .= "        return \$this->loadAndGetRepository('$repositoryFullClassName', '$defaultRepositoryFullClassName', '$tableName', '$entityFullClassName');\n";
            $sourceCode .= "    }\n";
            $sourceCode .= "\n";
        }

        $sourceCode .= "}\n";

        return $sourceCode;
    }

    /**
     * @param array $tableStructList
     * @return string
     */
    private function generateDynamicManagers(array $tableStructList)
    {
        $sourceCode = "<?php\n\n";

        // Namespace block
        if($this->entityManagerNamespace) {
            $sourceCode .= "namespace " . $this->entityManagerNamespace.";\n";
        }

        // Use block
        $sourceCode .= "use Anytime\ORM\EntityManager\Managers;\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\Manager;\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\EntityManager;\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\Connection;\n";

        foreach($tableStructList as $tableName => $tableStruct) {
            $entityName = $this->snakeToCamelCaseStringConverter->convert($tableName);
            $managerClassName = $entityName.'Manager';
            $managerFullClassName = $this->userManagerNamespace . "\\" . $managerClassName;
            $sourceCode .= "use $managerFullClassName;\n";
        }

        // Class
        $sourceCode .= "\n";
        $sourceCode .= "class DynamicManagers extends Managers\n";
        $sourceCode .= "{\n";

        // Properties
        $sourceCode .= "    private \$dynamicRepositories;\n";
        $sourceCode .= "    private \$entityManager;\n";

        // Constructor
        $sourceCode .= "    public function __construct(Connection \$connection, DynamicRepositories \$dynamicRepositories) {\n";
        $sourceCode .= "        \$this->dynamicRepositories = \$dynamicRepositories;\n";
        $sourceCode .= "        parent::__construct(\$connection);\n";
        $sourceCode .= "    }\n";

        // Methods
        foreach($tableStructList as $tableName => $tableStruct) {
            $entityName = $this->snakeToCamelCaseStringConverter->convert($tableName);
            $managerClassName = $entityName.'Manager';
            $managerGetterName = "get" . $managerClassName;
            $managerFullClassName = $this->userManagerNamespace . "\\" . $managerClassName;

            $entityRepositoryGetterCall = 'get'.$entityName.'EntityRepository';
            $entityManagerGetterCall = 'get'.$entityName.'Manager';

            $defaultManagerFullClassName = $this->entityManagerNamespace . "\\DefaultManager\\" . $managerClassName;

            $sourceCode .= "    /**\n";
            $sourceCode .= "     * @return $managerClassName|\\$defaultManagerFullClassName\n";
            $sourceCode .= "     */\n";
            $sourceCode .= "    public function $managerGetterName(): \\$defaultManagerFullClassName\n";
            $sourceCode .= "    {\n";
            $sourceCode .= "        return \$this->loadAndGetManager('$managerFullClassName','$defaultManagerFullClassName',\$this->dynamicRepositories->$entityRepositoryGetterCall(),\$this->entityManager);\n";
            $sourceCode .= "    }\n";
            $sourceCode .= "\n";
        }

        //setDynamicEntityManager
        $sourceCode .= "    /**\n";
        $sourceCode .= "     * @param DynamicEntityManager \$dynamicEntityManager\n";
        $sourceCode .= "     */\n";
        $sourceCode .= "    public function setDynamicEntityManager(DynamicEntityManager \$dynamicEntityManager)\n";
        $sourceCode .= "    {\n";
        $sourceCode .= "        \$this->entityManager = \$dynamicEntityManager;\n";
        $sourceCode .= "    }\n";

        //setDynamicEntityManager
        $sourceCode .= "    /**\n";
        $sourceCode .= "     * @return DynamicEntityManager";
        $sourceCode .= "     */\n";
        $sourceCode .= "    public function getDynamicEntityManager(): DynamicEntityManager\n";
        $sourceCode .= "    {\n";
        $sourceCode .= "        return \$this->entityManager;\n";
        $sourceCode .= "    }\n";

        //END CLASS
        $sourceCode .= "}\n";

        return $sourceCode;
    }


    /**
     * @param array $tableStruct
     * @param string $className
     * @param string $tableName
     * @return string
     */
    private function generateDynamicManager(array $tableStruct, string $className, string $tableName)
    {
        $createdMethods = [];

        $namespace = $this->entityManagerNamespace . '\\DefaultManager';

        $entityName = $this->snakeToCamelCaseStringConverter->convert($tableName);
        $entityFullClassName = $this->entityNamespace . '\\' . $entityName;
        $repositoryClassName = $entityName.'EntityRepository';
        $defaultRepositoryFullClassName = $this->entityManagerNamespace . '\\DefaultRepository\\' . $repositoryClassName;
        $userRepositoryFullClassName = $this->userEntityRepositoryNamespace . '\\' . $repositoryClassName;

        // Get primary keys and create args for findByPrimaryKey method
        $findByPrimaryKeyArgs = '';

        foreach($this->getPrimaryKeys($tableStruct) as $iPK => $pkey) {
            $findByPrimaryKeyArgs .= ($iPK === 0 ? '' : ', ') . $pkey['type'] . ' $' . lcfirst($this->snakeToCamelCaseStringConverter->convert($pkey['fieldName']));
        }

        $sourceCode = "<?php\n";
        $sourceCode .= "\n";
        $sourceCode .= "namespace $namespace;\n";
        $sourceCode .= "\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\Manager;\n";
        $sourceCode .= "use $entityFullClassName;\n";
        $sourceCode .= "use $defaultRepositoryFullClassName;\n";
        $sourceCode .= "\n";

        $sourceCode .= "/**\n";
        $sourceCode .= " * @method $repositoryClassName|\\$userRepositoryFullClassName getRepository()\n";
        $sourceCode .= " * @method $entityName|null findByPrimaryKey($findByPrimaryKeyArgs)\n";
        $sourceCode .= " */\n";
        $sourceCode .= "class $className extends Manager\n";
        $sourceCode .= "{\n";
        $sourceCode .= "    \n";

        foreach($tableStruct['indexes'] as $indexParts) {

            if (count($indexParts) < 1) {
                continue;
            }

            $explodedIndexParts = $this->explodeIndexParts($indexParts);

            foreach ($explodedIndexParts as $explodedIndexPart) {

                $findByMethodName = 'findBy';
                $findOneByMethodName = 'findOneBy';
                $updateByMethodName = 'updateBy';
                $deleteByMethodName = 'deleteBy';

                $phpParamList = '';
                $phpDocParamList = '';
                $phpParamListNoHintNoDefault = '';

                foreach ($explodedIndexPart as $iP => $indexPart) {
                    $columnType = $tableStruct['structure'][$indexPart['columnName']]['type'];

                    $columnNameCCase = $this->snakeToCamelCaseStringConverter->convert($indexPart['columnName']);
                    $findByMethodName .= ($iP > 0 ? 'And' : '') . $columnNameCCase;
                    $findOneByMethodName .= ($iP > 0 ? 'And' : '') . $columnNameCCase;
                    $updateByMethodName .= ($iP > 0 ? 'And' : '') . $columnNameCCase;
                    $deleteByMethodName .= ($iP > 0 ? 'And' : '') . $columnNameCCase;

                    $paramVarName = lcfirst($columnNameCCase);
                    $phpParamList .=
                        ($iP > 0 ? ', ' : '')
                        . ($columnType === 'date' ? ($indexPart['allowNull'] ? '' : '\DateTime') : $columnType)
                        . ' $' . $paramVarName
                        . ($indexPart['allowNull'] ? ' = NULL' : '')
                    ;
                    $phpParamListNoHintNoDefault .= ($iP > 0 ? ', ' : '') . '$' . $paramVarName;
                    $phpDocParamList .= "     * @param " . ($columnType === 'date' ? '\DateTime' : $columnType) . " \$$paramVarName\n";
                }

                $phpParamList .= ", bool \$useNamedParameters = false";
                $phpDocParamList .= "     * @param bool \$useNamedParameters \n";
                $phpParamListNoHintNoDefault .= ", \$useNamedParameters";

                if (in_array($findByMethodName, $createdMethods)) {
                    continue;
                }

                $createdMethods[] = $findByMethodName;

                // findBy...
                $sourceCode .= "    /**\n";
                $sourceCode .= $phpDocParamList;
                $sourceCode .= "     * @return $entityName" . "[]\n";
                $sourceCode .= "     */\n";
                $sourceCode .= "     public function $findByMethodName($phpParamList): array\n";
                $sourceCode .= "     {\n";
                $sourceCode .= "         return \$this->getRepository()->$findByMethodName($phpParamListNoHintNoDefault)->getSelectQuery()->fetchAll();\n";
                $sourceCode .= "     }\n";


                //findOneBy...
                $sourceCode .= "    /**\n";
                $sourceCode .= $phpDocParamList;
                $sourceCode .= "     * @return $entityName" . "|null\n";
                $sourceCode .= "     */\n";
                $sourceCode .= "     public function $findOneByMethodName($phpParamList)\n";
                $sourceCode .= "     {\n";
                $sourceCode .= "         return \$this->getRepository()->$findByMethodName($phpParamListNoHintNoDefault)->getSelectQuery()->fetchOne();\n";
                $sourceCode .= "     }\n";

                $parentQueryBuilderProxyClass = "\\" . $this->queryBuilderProxyNamespace . "\\" . $this->snakeToCamelCaseStringConverter->convert($tableName) . 'QueryBuilderUpdateProxy';

                $sourceCode .= "    /**\n";
                $sourceCode .= $phpDocParamList;
                $sourceCode .= "     * @return $parentQueryBuilderProxyClass\n";
                $sourceCode .= "     */\n";
                $sourceCode .= "     public function $updateByMethodName($phpParamList): $parentQueryBuilderProxyClass\n";
                $sourceCode .= "     {\n";
                $sourceCode .= "         return \$this->getRepository()->$updateByMethodName($phpParamListNoHintNoDefault);\n";
                $sourceCode .= "     }\n";

                $sourceCode .= "    /**\n";
                $sourceCode .= $phpDocParamList;
                $sourceCode .= "     * @return int\n";
                $sourceCode .= "     */\n";
                $sourceCode .= "     public function $deleteByMethodName($phpParamList): int\n";
                $sourceCode .= "     {\n";
                $sourceCode .= "         return (int)\$this->getRepository()->$deleteByMethodName($phpParamListNoHintNoDefault)->execute();\n";
                $sourceCode .= "     }\n";
            }
        }
        $sourceCode .= "}\n";

        return $sourceCode;
    }

    /**
     * @param array $tableStruct
     * @param string $className
     * @return string
     */
    private function generateDynamicRepository(array $tableStruct, string $className)
    {
        $createdMethods = [];

        $namespace = $this->entityManagerNamespace . '\\DefaultRepository';

        $sourceCode = "<?php\n";
        $sourceCode .= "\n";
        $sourceCode .= "namespace $namespace;\n";
        $sourceCode .= "\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\EntityRepository;\n";
        $sourceCode .= "use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;\n";
        $sourceCode .= "use Anytime\ORM\QueryBuilder\QueryBuilderInterface;\n";
        $sourceCode .= "use Anytime\ORM\QueryBuilder\QueryBuilderProxyInterface;\n";
        $sourceCode .= "\n";
        $sourceCode .= "class $className extends EntityRepository\n";
        $sourceCode .= "{\n";

        foreach($tableStruct['indexes'] as $indexParts) {

            if(count($indexParts) < 1) {
                continue;
            }

            $explodedIndexParts = $this->explodeIndexParts($indexParts);

            foreach($explodedIndexParts as $explodedIndexPart) {
                $tableName = $explodedIndexPart[0]['tableName'];
                $tableShortAlias = $this->getTableShortAlias($tableName);
                $findByMethodName = 'findBy';
                $updateByMethodName = 'updateBy';
                $deleteByMethodName = 'deleteBy';
                $phpParamList = '';
                $phpDocParamList = '';
                $where = '';
                $whereUpdate = '';
                $qbParameters = '';
                $qbNamedParameters = '';

                foreach ($explodedIndexPart as $iP => $indexPart) {
                    $columnType = $tableStruct['structure'][$indexPart['columnName']]['type'];
                    $dateFormat = $tableStruct['structure'][$indexPart['columnName']]['dateFormat'];
                    $columnNameCCase = $this->snakeToCamelCaseStringConverter->convert($indexPart['columnName']);
                    $findByMethodName .= ($iP > 0 ? 'And' : '') . $columnNameCCase;
                    $updateByMethodName .= ($iP > 0 ? 'And' : '') . $columnNameCCase;
                    $deleteByMethodName .= ($iP > 0 ? 'And' : '') . $columnNameCCase;
                    $paramVarName = lcfirst($columnNameCCase);
                    $phpParamList .= ($iP > 0 ? ', ' : '') . ($columnType === 'date' ? ($indexPart['allowNull'] ? '' : '\DateTime') : $columnType) . ' $' . $paramVarName . ($indexPart['allowNull'] ? ' = NULL' : '');
                    $phpDocParamList .= "     * @param " . ($columnType === 'date' ? '\DateTime' : $columnType) . " \$$paramVarName\n";
                    $where .= ($iP > 0 ? ' AND ' : '') . $tableShortAlias . '.' . $indexPart['columnName'] . ' = \' . ' . "(\$useNamedParameters ? ':$paramVarName' : '?').'";
                    $whereUpdate .= ($iP > 0 ? ' AND ' : '') . $indexPart['columnName'] . ' = :' . $paramVarName;

                    if($columnType === 'date') {
                        $qbParameters .= ($iP > 0 ? ', ' : '') . "(!is_null(\$$paramVarName) ? \$$paramVarName" . '->format("' . $dateFormat . '") : NULL)';
                        $qbNamedParameters .= "\n                " . ($iP > 0 ? ', ' : '') . "'" . $paramVarName . "' => (!is_null(\$$paramVarName) ? \$$paramVarName" . '->format("' . $dateFormat . '") : NULL)';
                    } else {
                        $qbParameters .= ($iP > 0 ? ', ' : '') . "\$$paramVarName";
                        $qbNamedParameters .= "\n                " . ($iP > 0 ? ', ' : '') . "'" . $paramVarName . "' => \$$paramVarName";
                    }

                }

                $phpParamListUpdate = $phpParamList;
                $phpParamList .= ", bool \$useNamedParameters = false";

                $phpDocParamListUpdate = $phpDocParamList;
                $phpDocParamList .= "     * @param bool \$useNamedParameters \n";
                $qbNamedParameters .= "\n";

                if (!in_array($findByMethodName, $createdMethods)) {
                    $createdMethods[] = $findByMethodName;

                    $sourceCode .= "    /**\n";
                    $sourceCode .= $phpDocParamList;
                    $sourceCode .= "     * @return \Anytime\ORM\QueryBuilder\QueryBuilderInterface\n";
                    $sourceCode .= "     */\n";
                    $sourceCode .= "    public function $findByMethodName($phpParamList): QueryBuilderInterface\n";
                    $sourceCode .= "    {\n";
                    $sourceCode .= "        \$queryBuilder = \$this->createQueryBuilder('$tableShortAlias');\n";
                    $sourceCode .= "        \$queryBuilder->where('$where')->setParameters(\$useNamedParameters \n            ? [$qbNamedParameters] \n            : [$qbParameters        ]);\n";
                    $sourceCode .= "        return \$queryBuilder;\n";
                    $sourceCode .= "    }\n";
                    $sourceCode .= "\n";
                }

                if (!in_array($updateByMethodName, $createdMethods)) {
                    $createdMethods[] = $updateByMethodName;

                    $parentQueryBuilderProxyClass = "\\" . $this->queryBuilderProxyNamespace . "\\" . $this->snakeToCamelCaseStringConverter->convert($tableName) . 'QueryBuilderUpdateProxy';

                    $sourceCode .= "\n    /**\n";
                    $sourceCode .= $phpDocParamListUpdate;
                    $sourceCode .= "     * @return $parentQueryBuilderProxyClass\n";
                    $sourceCode .= "     */\n";
                    $sourceCode .= "    public function $updateByMethodName($phpParamListUpdate): $parentQueryBuilderProxyClass\n";
                    $sourceCode .= "    {\n";
                    $sourceCode .= "        \$queryBuilderUpdateProxy = \$this->createQueryBuilderUpdateProxy();\n";
                    $sourceCode .= "        \$queryBuilderUpdateProxy->getQueryBuilder()->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)->where('$whereUpdate')->setParameters([$qbNamedParameters]        );\n";
                    $sourceCode .= "        return \$queryBuilderUpdateProxy;\n";
                    $sourceCode .= "    }\n";
                    $sourceCode .= "\n";
                }

                if (!in_array($deleteByMethodName, $createdMethods)) {
                    $whereDelete = $whereUpdate;

                    $createdMethods[] = $deleteByMethodName;

                    $sourceCode .= "\n    /**\n";
                    $sourceCode .= $phpDocParamListUpdate;
                    $sourceCode .= "     * @return QueryBuilderAbstract\n";
                    $sourceCode .= "     */\n";
                    $sourceCode .= "    public function $deleteByMethodName($phpParamListUpdate): QueryBuilderAbstract\n";
                    $sourceCode .= "    {\n";
                    $sourceCode .= "        \$queryBuilderDelete = \$this->createDeleteQueryBuilder();\n";
                    $sourceCode .= "        \$queryBuilderDelete->setQueryType(QueryBuilderAbstract::QUERY_TYPE_DELETE)->where('$whereDelete')->setParameters([$qbNamedParameters]);\n";
                    $sourceCode .= "        return \$queryBuilderDelete;\n";
                    $sourceCode .= "    }\n";
                    $sourceCode .= "\n";
                }
            }
        }

        $sourceCode .= "}\n";

        return $sourceCode;
    }

    /**
     * @param array $tableStruct
     * @return array
     */
    private function getPrimaryKeys(array $tableStruct): array
    {
        $primaryKeys = [];

        foreach($tableStruct['structure'] as $fieldStruct) {
            if($fieldStruct['keyType'] === 'PRI') {
                $primaryKeys[] = $fieldStruct;
            }
        }

        return $primaryKeys;
    }

    /**
     * @param string $tableName
     * @return string
     */
    private function getTableShortAlias(string $tableName): string
    {
        $alias = '';
        $parse = explode('_', $tableName);
        foreach($parse as $elem) {
            $alias .= $elem[0];
        }
        return $alias;
    }

    /**
     * @param array $indexParts
     * @return array
     */
    private function explodeIndexParts(array $indexParts): array
    {
        $explodedIndexes = [];

        foreach($indexParts as $i => $indexPart) {
            $explodedIndexes[] = [];

            for($ii = 0; $ii <= $i; $ii++) {
                $explodedIndexes[count($explodedIndexes)-1][] = $indexParts[$ii];
            }
        }

        return $explodedIndexes;
    }
}
