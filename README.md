# Anytime ORM

EntityORM is an ORM that use PDO to manage entities, repositories and managers optimized for best performances.

The architecture looks like this :

1. Entities generated classes based on database table and fields.
1. A generated entity manager used to access the generated managers and repositories. 
1. The default generated managers and repositories contains pre-built methods based on table indexes to retrieve data using indexes easily. 
1. The developer create their own managers and/or repositories if needed extending the default ones.

## Getting started

### PDO

As this ORM works with PDO you have to build a PDO object if you dont have created it yet.

```
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8', 'dbhost', 'dbname');
$pdo = new \PDO($dsn, 'dbuser', 'dbpassword');
```
We recommend to set the attribute "ATTR_EMULATE_PREPARES" and "ATTR_STRINGIFY_FETCHES" to false. It will prevent PDO to convert numeric values to strings.

```
$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
```

For postgreSQL you need to specify the default search_path :

```
$pdo->exec('SET search_path TO anytime');
```

### Instantiating the factory

The factory is used to get main classes as EntityManager, and the classes generators.
You have to set several parameters to the factory it will build all you need based on this.
This factory should be put in a service container.

```
$factory = new Factory();
```

Define the database type. Currently only mysql and postgresql are supported.
```
$factory->setDatabaseType(Factory::DATABASE_TYPE_MYSQL);
```

```
$factory->setDatabaseType(Factory::DATABASE_TYPE_POSTGRESQL);
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

Define the directory where the generated query builder update proxies classes are created. 
```
$factory->setQueryBuilderProxyDirectory(__DIR__ . '/../generated/query-builder);
```

Define the namespace of the generated query builder update proxies classes are created.
```
$factory->setQueryBuilderProxyNamespace('Dummy\\Project\\QueryBuilder');
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
 
use Anytime\ORM\EntityManager\Entity; 
 
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

#### Directory DefaultRepository

This directory contains the auto-generated Repositories containing methods based on table indexes.

#### Directory DefaultManager

This directory contains the auto-generated Managers containing methods based on table indexes.

### Instantiating the Entity Manager

After all these steps you are ready to start using the entity manager. Lets instantiate it...

You should put it in a service container and force return type to be the generated one for better autocompletion in your editor.

```
class SomeServiceContainer 
{ 
    public function getEntityManagerService(): DynamicEntityManager
    {
        $entityManager = $factory->createEntityManager($this->pdo);
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
 
### Using query builder to retrieve data
 
If you have to retrieve data based on several criteria, you will have to use a query builder.
 
#### Create the query builder 

```
$queryBuilder = $entityManager->managers->getCarManager()->createQueryBuilder('c');
``` 

#### Defining parameters used in the query
```
$queryBuilder->setParameters(['BMW']);
```

You can also use the named parameters :

```
$queryBuilder->setParameters(['carName' => 'BMW']);
```

#### Define where clause

```
$queryBuilder->where('c.brand = ?');
```

Or this if you have used the named parameters :

```
$queryBuilder->where('c.brand = :carName');
```

#### Prepare the query
```
$query = $queryBuilder->getSelectQuery();
```

#### Fetch only one result (the first one)
```
$car = $query->fetchOne();
```

#### Fetch one by one
```
while($car = $query->fetch()) {
    print_r($car);
}
```

#### Fetch all data
```
$cars = $query->fetchAll();
```

#### If you don't need entities you can change the fetch data format value
```
$query->setFetchDataFormat(SelectQuery::FETCH_DATA_FORMAT_ARRAY);
```

### Using filters

You can use filters to act on the results data retrieved before the ORM populates entities.

#### Create filters

* Create an object extending the class Anytime\ORM\EntityManager\Filter
* Define the method **apply() (see below)**
* In the apply method, you have to return the transformed value (or not transformed if not needed)

Example: 

```
<?php

namespace Dummy\Project\Filters;

use Anytime\ORM\EntityManager\Filter;

class FooFilter extends Filter
{
    /**
     * @param $inputValue
     * @param string $entityClass
     * @param string $propertyName
     * @param array $resultRow
     * @return mixed|null
     */
    public function apply($inputValue, string $entityClass, string $propertyName, array &$resultRow)
    {
        if($entityClass::getEntityPropertyType($propertyName) === 'string') {
            $inputValue = 'Foo ' . $inputValue;
        }

        return $inputValue;
    }
}

```

This filter will add the string "Foo " for each property of type "string".

Now if you want the filter to be applied, you have to instantiate it and add it to the entity manager.

When instantiating the Filter, you can specify the scope. If you do not, the scope will be global. It means that the filter will be applied to every properties of every entities.
The scope param is a list of key/value rows where the key is the entity class, and the value a list of regular expression to match the properties you want to apply the filter.

Note that the name of the filter must be unique.

```
$secureEntityManager->addFilter(new FooFilter(
    'My Super Foo Filter',
    [
        DummyEntity::class => [
            '^(foo|bar)$',                // Match "foo" and "bar" properties
            'date'                        // Match all properties containing thestring "date" 
        ]
    ]
));
```

You can add filters as many as you want, but beware! The performances of the orm can but substancially degraded, especially if you keep it in a global scope.

### Create custom repository and managers

Let's take the "car" table and add a foreign key to the owner table for the example.

```
CREATE TABLE `car` (
  `id_car` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `brand` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_car`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 
CREATE TABLE `owner` (
  `id_owner` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_owner`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

We create a custom repository class in the configured directory for this table.
In our case you will create it in the "src/EntityRepository/" directory :

```
<?php
 
namespace Dummy\Project\EntityRepository;
 
use Anytime\ORM\EntityManager\EntityRepository;
 
class CarEntityRepository extends EntityRepository
{
}
```

Now add a custom method that will make a join between "car" and "owner" : 

```
<?php
 
namespace Dummy\Project\EntityRepository;
 
class CarEntityRepository extends \Dummy\Project\EntityManager\DefaultRepository\CarEntityRepository
{
    /**
     * @return \Anytime\ORM\QueryBuilder\QueryBuilderInterface
     */
    public function findCarWithOwner()
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->join('INNER JOIN owner o ON o.id_owner = c.owner_id');
        return $queryBuilder;
    }
}
```

This repository class will be loaded now instead of the DefaultRepositoryClass

Now let's create a manager class in the configured directory. In our case it is "src/Manager/" :

```
<?php

namespace Dummy\Project\Manager;

class CarManager extends \Dummy\Project\EntityManager\DefaultManager\CarManager
{
}
```

Now let's add a method that use the repository : 


```
<?php

namespace Dummy\Project\Manager;

class CarManager extends \Dummy\Project\EntityManager\DefaultManager\CarManager
{
    public function findCarsByOwnerName(string $ownerName)
    {
        $repository = $this->getRepository();
        $queryBuilder = $repository->createCarWithOwnerQueryBuilder();

        $queryBuilder->setParameters([
            'ownerName' => $ownerName
        ]);
        $queryBuilder->where('o.name = :ownerName');


        return $queryBuilder->getSelectQuery()->fetchAll();
    }
}
```

It will returns all the cars linked to the owner called "John Smith".

## Insert data

### Insert a single entity

```
$car = (new Car())
    ->setBrand('Audi')
    ->setOwnerId(1)
;
$entityManager->insert($car);
echo 'record inserted with ID #'.$car->getIdCar();
```

### Insert multiple entities
```
$entityManager->insert([$entity1, $entity2,..., $entityN]);
```

## Update data

### Update a single entity
```
$entity->setSomeProperty('new value');
$entityManager->update($entity);
```

### Update multiple entities
```
$entityManager->update([$entity1,$entity2,...,$entityN]);
```

### Massive update based on where criteria
```
$qb = $entityManager->managers->getCarManager()->createUpdateQueryBuilder();
$qb->setParameters([
    'ownerId' => 20
]);
$qb->where('car.owner_id = :ownerId');
$qb->setFieldsToUpdate([
    'brand' => 'Minicooper'
]);
$affectedRows = $qb->getUpdateQuery()->execute();

echo $affectedRows . ' rows updated';
```

### Massive update using methods based on indexes

We suppose that we have an multiple index on te field owner_id and brand and a field "field_a" and "field_b"

```
$affectedRows = $entityManager->managers->getCarManager()->updateByOwnerIdAndBrand($ownerId, $brand)
    ->setFieldA('A')
    ->setFieldB('B')
    ->execute()
;
```

You can also use an expression instead of a specific value. 

To do that you have to instantiate the **\Anytime\ORM\QueryBuilder\Expression** class, and define the expression string to use. You can use the **%FIELD%** string in the expression, it will be replaced by the table field name matching the current setter.

```
$affectedRows = $entityManager->managers->getCarManager()->updateByOwnerIdAndBrand($ownerId, $brand)
    ->setFieldA('A')
    ->setFieldB(new Expr('LOWER(%FIELD%)'))
    ->execute()
;
```

In this example the final SQL UPDATE will be something like : 

```
UPDATE car SET field_A = 'A' AND field_b = LOWER(field_b);
```

## Delete data

### Delete a single entity

```
$entityManager->delete($entity);
```

### Delete multiple entities
```
$entityManager->delete([$entity1,$entity2,...,$entityN]);
```

### Massive delete based on where criteria
```
$qb = $entityManager->managers->getCarManager()->createDeleteQueryBuilder();
$qb->setParameters(['ownerId' => 1]);
$qb->where('car.owner_id = :ownerId');
$affectedRows = $qb->getDeleteQuery()->execute();

echo $affectedRows . ' rows deleted.';
```

### Massive delete using methods based on indexes

We suppose that we have an multiple index on te field owner_id and brand

```
$affectedRows = $entityManager->managers->getCarManager()->deleteByOwnerIdAndBrand($ownerId, $brand);
```
