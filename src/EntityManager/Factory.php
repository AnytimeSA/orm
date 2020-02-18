<?php

namespace Anytime\ORM\EntityManager;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\Generator\EntityGenerator\EntityGeneratorInterface;
use Anytime\ORM\Generator\EntityGenerator\EntityGenerator;
use Anytime\ORM\Generator\EntityGenerator\MySqlTableStructureRetriever;
use Anytime\ORM\Generator\EntityGenerator\PostgreSqlTableStructureRetriever;
use Anytime\ORM\Generator\EntityManagerGenerator\EntityManagerGenerator;
use Anytime\ORM\Generator\EntityManagerGenerator\EntityManagerGeneratorInterface;
use Anytime\ORM\Generator\QueryBuilderGenerator\QueryBuilderGenerator;
use Anytime\ORM\Generator\QueryBuilderProxyGenerator\QueryBuilderProxyGenerator;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;

class Factory
{
    const DATABASE_TYPE_MYSQL = 'mysql';
    const DATABASE_TYPE_POSTGRESQL = 'postgresql';

    /**
     * @var string
     */
    private $databaseType = self::DATABASE_TYPE_MYSQL;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    private $snakeToCamelCaseStringConverter;

    /**
     * @var FilterCollection
     */
    private $filterCollection;

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
    private $userManagerNamespace;

    /**
     * @var string
     */
    private $queryBuilderProxyDirectory;

    /**
     * @var string
     */
    private $queryBuilderProxyNamespace;

    /**
     * Factory constructor.
     */
    public function __construct()
    {
        $this->snakeToCamelCaseStringConverter = new SnakeToCamelCaseStringConverter();
        $this->filterCollection = new FilterCollection();
    }

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
     * @param string $queryBuilderDirectory
     * @return Factory
     */
    public function setQueryBuilderProxyDirectory(string $queryBuilderProxyDirectory): Factory
    {
        $this->queryBuilderProxyDirectory = $queryBuilderProxyDirectory;
        return $this;
    }

    /**
     * @param string $queryBuilderNamespace
     * @return Factory
     */
    public function setQueryBuilderProxyNamespace(string $queryBuilderProxyNamespace): Factory
    {
        $this->queryBuilderProxyNamespace = $queryBuilderProxyNamespace;
        return $this;
    }

    /**
     * Create a MySQL entity manager based on the settings
     * @param \PDO $pdo
     * @return EntityManager
     */
    public function createEntityManager(\PDO $pdo)
    {
        $connection = new Connection($pdo);

        $this->checkSetting();
        $this->checkDynamicClasses();

        $dynamicRepositoriesClass = $this->entityManagerNamespace . '\\DynamicRepositories';
        $dynamicManagersClass = $this->entityManagerNamespace . '\\DynamicManagers';

        $queryBuilderFactory = new QueryBuilderFactory(
            $connection,
            $this->snakeToCamelCaseStringConverter,
            $this->filterCollection,
            $this->databaseType,
            $this->entityManagerNamespace,
            $this->queryBuilderProxyNamespace
        );

        $dynamicRepositories = new $dynamicRepositoriesClass($connection, $this->snakeToCamelCaseStringConverter, $queryBuilderFactory);
        $dynamicManagers = new $dynamicManagersClass($connection, $dynamicRepositories);

        $dynamicEntityManagerClass = $this->entityManagerNamespace . '\\DynamicEntityManager';
        $dynamicEntityManager = new $dynamicEntityManagerClass(
            $connection,
            $this->snakeToCamelCaseStringConverter,
            $dynamicRepositories,
            $dynamicManagers,
            $queryBuilderFactory,
            $this->filterCollection,
            $this->databaseType
        );

        $dynamicManagers->setDynamicEntityManager($dynamicEntityManager);
        return $dynamicEntityManager;


    }

    /**
     * @param \PDO $pdo
     * @return EntityGeneratorInterface
     */
    public function createEntityGenerator(\PDO $pdo): EntityGeneratorInterface
    {
        return new EntityGenerator(
            $this->snakeToCamelCaseStringConverter,
            $this->getTableStructureRetriever($pdo),
            $this->filterCollection,
            $this->entityDirectory,
            $this->entityNamespace
        );


    }

    /**
     * @param \PDO $pdo
     * @return EntityManagerGeneratorInterface
     */
    public function createEntityManagerGenerator(\PDO $pdo): EntityManagerGeneratorInterface
    {
        return new EntityManagerGenerator(
            $this->snakeToCamelCaseStringConverter,
            $this->getTableStructureRetriever($pdo),
            $this->entityManagerDirectory,
            $this->entityManagerNamespace,
            $this->userEntityRepositoryDirectory,
            $this->userEntityRepositoryNamespace,
            $this->userManagerDirectory,
            $this->userManagerNamespace,
            $this->entityNamespace,
            $this->queryBuilderProxyDirectory,
            $this->queryBuilderProxyNamespace
        );


    }

    /**
     * @param \PDO $pdo
     * @return QueryBuilderProxyGenerator
     */
    public function createQueryBuilderProxyGenerator(\PDO $pdo)
    {
        return new QueryBuilderProxyGenerator(
            $this->snakeToCamelCaseStringConverter,
            $this->getTableStructureRetriever($pdo),
            $this->databaseType,
            $this->queryBuilderProxyDirectory,
            $this->queryBuilderProxyNamespace
        );
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

    /**
     * @param \PDO $pdo
     * @return MySqlTableStructureRetriever|PostgreSqlTableStructureRetriever
     */
    private function getTableStructureRetriever(\PDO $pdo)
    {
        switch($this->databaseType) {
            case self::DATABASE_TYPE_MYSQL :
                return new MySqlTableStructureRetriever($pdo);
            case self::DATABASE_TYPE_POSTGRESQL :
                return new PostgreSqlTableStructureRetriever($pdo);
            default:
                throw new \InvalidArgumentException('Bad database type "'.$this->databaseType.'"');
        }
    }
}
