<?php

namespace Anytime\ORM\Generator\EntityManagerGenerator;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\Generator\EntityGenerator\TableStructureRetrieverInterface;

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
        string $entityNamespace)
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
        ;
    }

    /**
     * @param string $entityManagerDirectory
     * @return EntityManagerGenerator
     */
    public function setEntityManagerDirectory(string $entityManagerDirectory): EntityManagerGenerator
    {
        if(is_dir($entityManagerDirectory) && is_writable($entityManagerDirectory)) {
            $this->entityManagerDirectory = $entityManagerDirectory;
        } else {
            throw new \RuntimeException('The entity manager directory should exists and be writable.');
        }
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
        if(is_dir($userEntityRepositoryDirectory)) {
            $this->userEntityRepositoryDirectory = $userEntityRepositoryDirectory;
        } else {
            throw new \RuntimeException('The user entity repository directory should exists.');
        }
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
        if(is_dir($userManagerDirectory)) {
            $this->userManagerDirectory = $userManagerDirectory;
        } else {
            throw new \RuntimeException('The user manager directory should exists.');
        }
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
     * @param array $tableList
     */
    public function generate(array $tableList = [])
    {
        $tableStructList = $this->tableStructureRetriever->retrieve($tableList);

        $sourceCode = $this->generateDynamicRepositories($tableStructList);
        file_put_contents($this->entityManagerDirectory . '/DynamicRepositories.php', $sourceCode);

        $sourceCode = $this->generateDynamicManagers($tableStructList);
        file_put_contents($this->entityManagerDirectory . '/DynamicManagers.php', $sourceCode);

        $sourceCode = $this->generateDynamicEntityManager();
        file_put_contents($this->entityManagerDirectory . '/DynamicEntityManager.php', $sourceCode);

        $managersDir = $this->entityManagerDirectory . '/Manager';
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
            $className = $this->snakeToCamelCaseStringConverter->convert($tableName).'Manager';
            $sourceCode = $this->generateDynamicManager($tableStruct, $className);
            file_put_contents($this->entityManagerDirectory . '/Manager/'.$className.'.php', $sourceCode);
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
        $sourceCode .= "    public function __construct(\\PDO \$pdo, SnakeToCamelCaseStringConverter \$snakeToCamelCaseStringConverter, DynamicRepositories \$dynamicRepositories, DynamicManagers \$dynamicManagers, QueryBuilderFactory \$queryBuilderFactory, string \$databaseType)\n";
        $sourceCode .= "    {\n";
        $sourceCode .= "        \$this->repositories = \$dynamicRepositories;\n";
        $sourceCode .= "        \$this->managers = \$dynamicManagers;\n";
        $sourceCode .= "        parent::__construct(\$pdo, \$snakeToCamelCaseStringConverter, \$queryBuilderFactory, \$databaseType);\n";
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

            $sourceCode .= "    /**\n";
            $sourceCode .= "     * @return $repositoryClassName|EntityRepository\n";
            $sourceCode .= "     */\n";
            $sourceCode .= "    public function $repositoryGetterName(): EntityRepository\n";
            $sourceCode .= "    {\n";
            $sourceCode .= "        return \$this->loadAndGetRepository('$repositoryFullClassName', '$tableName', '$entityFullClassName');\n";
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

        foreach($tableStructList as $tableName => $tableStruct) {
            $entityName = $this->snakeToCamelCaseStringConverter->convert($tableName);
            $managerClassName = $entityName.'Manager';
            $managerFullClassName = $this->userManagerNamespace . "\\" . $managerClassName;
            $defaultManagerFullClassname = $this->entityManagerNamespace . '\\Manager\\' . $managerClassName;
            $sourceCode .= "use $managerFullClassName;\n";
            $sourceCode .= "use $defaultManagerFullClassname\n";
        }

        // Class
        $sourceCode .= "\n";
        $sourceCode .= "class DynamicManagers extends Managers\n";
        $sourceCode .= "{\n";

        // Properties
        $sourceCode .= "    private \$dynamicRepositories;\n";

        // Constructor
        $sourceCode .= "    public function __construct(\\PDO \$pdo, DynamicRepositories \$dynamicRepositories) {\n";
        $sourceCode .= "        \$this->dynamicRepositories = \$dynamicRepositories;\n";
        $sourceCode .= "        parent::__construct(\$pdo);\n";
        $sourceCode .= "    }\n";

        // Methods
        foreach($tableStructList as $tableName => $tableStruct) {
            $entityName = $this->snakeToCamelCaseStringConverter->convert($tableName);
            $managerClassName = $entityName.'Manager';
            $managerGetterName = "get" . $managerClassName;
            $managerFullClassName = $this->userManagerNamespace . "\\" . $managerClassName;
            //$entityFullClassName = $this->entityNamespace . '\\' . $entityName;

            $entityRepositoryGetterCall = 'get'.$entityName.'EntityRepository';

            $defaultManagerClassname = $this->entityManagerNamespace . '\\Manager\\' . $managerClassName;

            $sourceCode .= "    /**\n";
            $sourceCode .= "     * @return $managerClassName|$defaultManagerClassname\n";
            $sourceCode .= "     */\n";
            $sourceCode .= "    public function $managerGetterName(): Manager\n";
            $sourceCode .= "    {\n";
            $sourceCode .= "        return \$this->loadAndGetManager('$managerFullClassName',\$this->dynamicRepositories->$entityRepositoryGetterCall());\n";
            $sourceCode .= "    }\n";
            $sourceCode .= "\n";
        }

        $sourceCode .= "}\n";

        return $sourceCode;
    }


    /**
     * @param array $tableStruct
     * @param string $className
     * @return string
     */
    private function generateDynamicManager(array $tableStruct, string $className)
    {
        $namespace = $this->entityManagerNamespace . '\\Manager';

        $sourceCode = "<?php\n";
        $sourceCode .= "\n";
        $sourceCode .= "namespace $namespace;\n";
        $sourceCode .= "\n";
        $sourceCode .= "use Anytime\ORM\EntityManager\Manager;\n";
        $sourceCode .= "\n";
        $sourceCode .= "class $className extends Manager\n";
        $sourceCode .= "{\n";

        $sourceCode .= "}\n";

        return $sourceCode;
    }
}