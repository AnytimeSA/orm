<?php

namespace DVE\EntityORM\Generator\EntityManagerGenerator;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;
use DVE\EntityORM\Generator\EntityGenerator\TableStructureRetrieverInterface;

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
     * @param string $entityNamespace
     */
    public function __construct(
        SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter,
        TableStructureRetrieverInterface $tableStructureRetriever,
        string $entityManagerDirectory,
        string $entityManagerNamespace,
        string $userEntityRepositoryDirectory,
        string $userEntityRepositoryNamespace,
        string $entityNamespace)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->tableStructureRetriever = $tableStructureRetriever;
        $this
            ->setEntityManagerDirectory($entityManagerDirectory)
            ->setEntityManagerNamespace($entityManagerNamespace)
            ->setUserEntityRepositoryDirectory($userEntityRepositoryDirectory)
            ->setUserEntityRepositoryNamespace($userEntityRepositoryNamespace)
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
     * @param string $entityNamespace
     * @return EntityManagerGenerator
     */
    public function setEntityNamespace(string $entityNamespace): EntityManagerGenerator
    {
        $this->entityNamespace = $entityNamespace;
        return $this;
    }

    public function generate(array $tableList = [])
    {
        $tableStructList = $this->tableStructureRetriever->retrieve($tableList);

        $sourceCode = "<?php\n\n";

        // Namespace block
        if($this->entityManagerNamespace) {
            $sourceCode .= "namespace " . $this->entityManagerNamespace.";\n";
        }

        // Use block
        $sourceCode .= "\n";
        $sourceCode .= "use DVE\EntityORM\EntityManager\EntityManager;\n";
        $sourceCode .= "use DVE\EntityORM\EntityManager\EntityRepository;\n";

        foreach($tableStructList as $tableName => $tableStruct) {
            $repositoryClassName = $this->snakeToCamelCaseStringConverter->convert($tableName).'EntityRepository';
            $sourceCode .= "use ".$this->userEntityRepositoryNamespace."\\$repositoryClassName;\n";
        }

        $sourceCode .= "\n";

        // Class block
        $sourceCode .= "class DynamicEntityManager extends EntityManager\n";
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

        $sourceCode .= "}";

        file_put_contents($this->entityManagerDirectory . '/DynamicEntityManager.php', $sourceCode);
    }
}