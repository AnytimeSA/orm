<?php

namespace DVE\EntityORM\EntityManager;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;
use DVE\EntityORM\Generator\EntityGenerator\EntityGeneratorInterface;
use DVE\EntityORM\Generator\EntityGenerator\MySqlEntityGenerator;
use DVE\EntityORM\Generator\EntityGenerator\MySqlTableStructureRetriever;
use DVE\EntityORM\Generator\EntityManagerGenerator\EntityManagerGenerator;
use DVE\EntityORM\Generator\EntityManagerGenerator\EntityManagerGeneratorInterface;

class Factory
{
    const DATABASE_TYPE_MYSQL = 'mysql';

    /**
     * @var string
     */
    private $entityDirectory;

    /**
     * @var string
     */
    private $entityNamespace;

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
    private $databaseType = self::DATABASE_TYPE_MYSQL;

    /**
     * @var string
     */
    private $userManagerNamespace;

    /**
     * This is the directory where the auto-generated entities are created
     *
     * @param string $entityDirectory
     * @return Factory
     */
    public function setEntityDirectory(string $entityDirectory): Factory
    {
        $this->entityDirectory = $entityDirectory;
        return $this;
    }

    /**
     * This is the namespace applied to the generated entities
     *
     * @param string $entityNamespace
     * @return Factory
     */
    public function setEntityNamespace(string $entityNamespace): Factory
    {
        $this->entityNamespace = $entityNamespace;
        return $this;
    }

    /**
     * This is the directory where the generated entity manager (DynamicEntityManager) will be created
     *
     * @param string $entityManagerDirectory
     * @return Factory
     */
    public function setEntityManagerDirectory(string $entityManagerDirectory): Factory
    {
        $this->entityManagerDirectory = $entityManagerDirectory;
        return $this;
    }

    /**
     * This is the namespace applied to the generated entity manager (DynamicEntityManager)
     *
     * @param string $entityManagerNamespace
     * @return Factory
     */
    public function setEntityManagerNamespace(string $entityManagerNamespace): Factory
    {
        $this->entityManagerNamespace = $entityManagerNamespace;
        return $this;
    }

    /**
     * This is the directory where developers using the ORM have to create the entity repositories with custom methods
     *
     * @param string $userEntityRepositoryDirectory
     * @return Factory
     */
    public function setUserEntityRepositoryDirectory(string $userEntityRepositoryDirectory): Factory
    {
        $this->userEntityRepositoryDirectory = $userEntityRepositoryDirectory;
        return $this;
    }

    /**
     * This is the namespace that the custom entity repositories should have
     *
     * @param string $userEntityRepositoryNamespace
     * @return Factory
     */
    public function setUserEntityRepositoryNamespace(string $userEntityRepositoryNamespace): Factory
    {
        $this->userEntityRepositoryNamespace = $userEntityRepositoryNamespace;
        return $this;
    }

    /**
     * This is the directory where developers using the ORM have to create the managers with custom methods
     *
     * @param string $userManagerDirectory
     * @return Factory
     */
    public function setUserManagerDirectory(string $userManagerDirectory): Factory
    {
        $this->userManagerDirectory = $userManagerDirectory;
        return $this;
    }

    /**
     * This is the namespace that the custom managers should have
     *
     * @param string $userManagerNamespace
     * @return Factory
     */
    public function setUserManagerNamespace(string $userManagerNamespace): Factory
    {
        $this->userManagerNamespace = $userManagerNamespace;
        return $this;
    }

    /**
     * @param string $databaseType
     * @return Factory
     */
    public function setDatabaseType(string $databaseType): Factory
    {
        $this->databaseType = $databaseType;
        return $this;
    }

    /**
     * Create a MySQL entity manager based on the settings
     * @param \PDO $pdo
     * @return EntityManager
     */
    public function createEntityManager(\PDO $pdo)
    {
        $this->checkSetting();
        $this->checkDynamicClasses();

        $dynamicRepositoriesClass = $this->entityManagerNamespace . '\\DynamicRepositories';
        $dynamicManagersClass = $this->entityManagerNamespace . '\\DynamicManagers';

        $dynamicRepositories = new $dynamicRepositoriesClass($pdo);
        $dynamicManagers = new $dynamicManagersClass($pdo, $dynamicRepositories);

        $dynamicEntityManagerClass = $this->entityManagerNamespace . '\\DynamicEntityManager';
        return new $dynamicEntityManagerClass($dynamicRepositories, $dynamicManagers);
    }

    /**
     * @param \PDO $pdo
     * @return EntityGeneratorInterface
     */
    public function createEntityGenerator(\PDO $pdo): EntityGeneratorInterface
    {
        switch($this->databaseType) {
            case self::DATABASE_TYPE_MYSQL :
                return new MySqlEntityGenerator(
                    new SnakeToCamelCaseStringConverter(),
                    new MySqlTableStructureRetriever($pdo),
                    $this->entityDirectory,
                    $this->entityNamespace
                );
        }

        throw new \InvalidArgumentException('Bad database type "'.$this->databaseType.'"');
    }

    /**
     * @param \PDO $pdo
     * @return EntityManagerGeneratorInterface
     */
    public function createEntityManagerGenerator(\PDO $pdo): EntityManagerGeneratorInterface
    {
        switch($this->databaseType) {
            case self::DATABASE_TYPE_MYSQL :
                return new EntityManagerGenerator(
                    new SnakeToCamelCaseStringConverter(),
                    new MySqlTableStructureRetriever($pdo),
                    $this->entityManagerDirectory,
                    $this->entityManagerNamespace,
                    $this->userEntityRepositoryDirectory,
                    $this->userEntityRepositoryNamespace,
                    $this->userManagerDirectory,
                    $this->userManagerNamespace,
                    $this->entityNamespace
                );
        }

        throw new \InvalidArgumentException('Bad database type "'.$this->databaseType.'"');
    }

    /**
     * Check the current setting
     */
    private function checkSetting()
    {
        if(is_null($this->entityDirectory)) {
            throw new \InvalidArgumentException('"entityDirectory" property is not defined');
        }

        if(is_null($this->entityNamespace)) {
            throw new \InvalidArgumentException('"entityNamespace" property is not defined');
        }

        if(is_null($this->entityManagerDirectory)) {
            throw new \InvalidArgumentException('"entityManagerDirectory" property is not defined');
        }

        if(is_null($this->entityManagerNamespace)) {
            throw new \InvalidArgumentException('"entityManagerNamespace" property is not defined');
        }

        if(is_null($this->userEntityRepositoryDirectory)) {
            throw new \InvalidArgumentException('"userEntityRepositoryDirectory" property is not defined');
        }

        if(is_null($this->userEntityRepositoryNamespace)) {
            throw new \InvalidArgumentException('"userEntityRepositoryNamespace" property is not defined');
        }

        if(is_null($this->userManagerDirectory)) {
            throw new \InvalidArgumentException('"userManagerDirectory" property is not defined');
        }

        if(is_null($this->userManagerNamespace)) {
            throw new \InvalidArgumentException('"userManagerNamespace" property is not defined');
        }
    }

    /**
     * Check if the dynamic classes are generated properly
     */
    private function checkDynamicClasses()
    {
        $classes = [
            'DynamicEntityManager',
            'DynamicManagers',
            'DynamicRepositories'
        ];

        foreach($classes as $class) {
            $fullClass = $this->entityManagerNamespace . '\\' . $class;

            if(!class_exists($fullClass)) {
                throw new \RuntimeException("The dynamic class \"$fullClass\" does not exists. Please generate the dynamic classes by using the generators. You can instantiate with it Factory::createEntityManagerGenerator() and Factory::createEntityGenerator() to generate entities.");
            }
        }
    }
}