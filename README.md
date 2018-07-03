# Entity ORM

EntityORM is an ORM that use PDO to manage entities, repositories and managers optimized for best performances.

The architecture looks like this :

1. Entities generated classes based on database table and fields.
1. A generated entity manager used to access the generated managers and repositories. By default a DefaultManager or a default DefaultEntityRepository is used. 
1. The developer create their own managers and/or repositories if needed extending the default ones.

## Getting started

### PDO

As this ORM works with PDO you have to build a PDO object if you dont have created it yet.

```
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8', 'dbhost', 'dbname');
$pdo = new \PDO($dsn, 'dbuser', 'dbpassword');
```

### Instantiating the factory

The factory is used to get main classes as EntityManager, and the classes generators.
You have to set several parameters to the factory it will build all you need based on this.
This factory should be put in a service container.

```
$factory = new Factory();
```

Define the database type. Currently only mysql is supported.
```
$factory->setDatabaseType(Factory::DATABASE_TYPE_MYSQL);
```

Define the directory where the auto-generated entities are written.
```
$factory->setEntityDirectory(__DIR__ . '/../generated/entity');
```

Define the name space that the entities should have.
```
$factory->setEntityNamespace('Dummy\\Project\\Entities');
```

Define the directory where the generated entity manager is written.
```
$factory->setEntityManagerDirectory(__DIR__ . '/../generated/entity-manager');
```

Define the name space of the generated entity manager.
```
$factory->setEntityManagerNamespace('Dummy\\Project\\EntityManager');
```

Define the directory of the custom entity repositories classes. The custom entity repositories are repositories the developers create by extending the default one because they need to add some methods related to this repository.
```
$factory->setUserEntityRepositoryDirectory(__DIR__ . '/../src/EntityRepository');
```

Define the namespace that the custom entity repositories have.
```
$factory->setUserEntityRepositoryNamespace('Dummy\\Project\\EntityRepository');
```

Define the directory where the custom managers classes are created. The custom managers classes are managers created by developers by extinding the default one because they need to add some methods related to this manager.
```
$factory->setUserManagerDirectory(__DIR__ . '/../src/Manager');
```

Define the  namespace that custom managers should have.
```
$factory->setUserManagerNamespace('Dummy\\Project\\Manager');
```

### Composer

You also have to configure your composer.json to make the autoloaded able to load the generated classes.
To do that you have to spcify each directory and namespace in the autoload property of composer.json.



```
    ...
    
    "autoload": {
        "psr-4": {
            "Dummy\\Project\\": "src/",
            "Dummy\\Project\\Entities\\": "generated/entity/",
            "Dummy\\Project\\EntityManager\\": "generated/entity-manager/"
        }
    }
    
    ...
        
```

Don't forget to make a **composer update** after that.

### Generating entities and entity manager

Now you have a factory available and fully configured, you can use it to create the generator classes.
When you generate the entities and entity manager, the generator will read the database structure and generate it based on it.
This operation have to be done only one time. The first time or when you need to refresh the classes because the database structure has changed.

```
$entityGenerator = $factory->createEntityGenerator($pdo);
$entityGenerator->generate();
```

```
$entityManagerGenerator = $factory->createEntityManagerGenerator($pdo);
$entityManagerGenerator->generate();
```

Done! Now check the defined directories and you will see all your entities and the entity manager perfectly generated.

Lets have a look on the generated classes.

#### The entities

Based on this table structure : 
 
```
CREATE TABLE `car` (
  `id_car` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brand` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_car`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Our generated entity class should looks like this :

```
namespace Dummy\Project\Entities;
 
use DVE\EntityORM\EntityManager\Entity; 
 
class Car extends Entity
{
    const TABLENAME = 'car';
    const PRIMARY_KEYS = ['id_car'];
 
    protected $data = [
        'id_car' => 0,
        'name' => '',
    ];
 
    public function setIdCar(int $id): Car
    {
        $this->data['id_car'] = $id;
        return $this;
    }
 
    public function getIdCar(): int
    {
        return (int)$this->data['id_car'];
    } 
    
    public function setBrand(string $id): Car
    {
        $this->data['brand'] = $id;
        return $this;
    }
 
    public function getBrand(): string
    {
        return (string)$this->data['brand'];
    }     
    
}
```

#### DynamicRepositories

This class contains a getter methods for each repositories of each table. In our example we will have the method 

```
    public function getCarEntityRepository(): EntityRepository
```

The repositories classes will help you to create query builder used by the managers.

#### DynamicManager

This class contains a getter methods for each manager of each table. In our example we will have the method 

```
    public function getCarManager(): Manager;
```

The managers will help you to find data related to the table in the database.


### Instantiating the Entity Manager

After all these steps you are ready to start using the entity manager. Lets instantiate it...

You should put it in a service container and force return type to be the generated one for better autocompletion in your editor.

```
class SomeServiceContainer 
{ 
    public function getEntityManagerService(): DynamicEntityManager
    {
        $entityManager = $factory->createEntityManager($pdo);
    }
}
```

Like this when using it you will have the auto-completion for all the existing methods.


## Retrieving data

To retrieve data you will have to use the managers. A manager will always use the repository related to the same table. Before implementing your own manager try to use the existing methods.

### findByPrimaryKey

This method is used to retrieve a record with the primary key value. The composite primary keys are also supported.

```
$car = $entityManager->managers->getCarManager()->findByPrimaryKey(1);
print_r($car);
```

If you have a record with the ID "1" it will return a Car entity fully hydrated.

If you have to retrieve an entity with a composite primary key do like this :
 
 ```
 $someCompopsiteEntity = $entityManager->managers->getSomeCompositeManager()->findByPrimaryKey(1, 2);
 print_r($someCompositeEntity);
 ```