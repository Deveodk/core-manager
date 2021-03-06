[![Build Status](https://travis-ci.org/Deveodk/core-manager.svg?branch=master)](https://travis-ci.org/Deveodk/core-manager)
[![Coverage Status](https://coveralls.io/repos/github/Deveodk/core-manager/badge.svg?branch=master)](https://coveralls.io/github/Deveodk/core-manager?branch=master)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)
[![Latest Stable Version](https://poser.pugx.org/deveodk/core-manager/v/stable)](https://packagist.org/packages/deveodk/core-manager)
[![Total Downloads](https://poser.pugx.org/deveodk/core-manager/downloads)](https://packagist.org/packages/deveodk/core-manager)
[![License](https://poser.pugx.org/deveodk/core-manager/license)](https://packagist.org/packages/deveodk/core-manager)

## core-manager

> To be used explicitly with Core by Deveo

## Requirements

This package requires the following:

* Composer
* Core by Deveo
* Laravel 5.5+
* PHP 7.1+

## Installation

Installation via Composer:

```bash
composer require deveodk/core-manager
```

## Disclaimer

Core manger are an opinionated approach to designing modern Application Programming Interfaces (APIs). Every component is specifically designed to be used with Core by Deveo and is therefore not compatible with other frameworks such as standard Laravel.

## What it does

Core manager is the layer between your database and API endpoints. With Core manager we enable advanced and secure database
queries over standard Query string parameters.

## Dictionary

Here you will find commonly used phrases and their meaning

| Term                 	| Meaning                                                                                                                                                                                                              	|
|----------------------	|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------	|
| Entity               	| An entity is equivalent to an Laravel model. but is designed to be compatible with Core                                                                                                                              	|
| Repository           	| An repository is like a bridge that interacts with an Entity and the requested resource. Every CRUD action should go through the repository                                                                          	|
| Resource transformer 	| An resource transformer are part of what makes manager so special. The resource transformer is responsible for which fields and relations should be outputtet. This will also format the response for the given type 	|
| Formatter            	| An formatter is whats responsible for formatting the array of data into one of three formats JSON, YAML & XML. The formatter can be extended to other formats.                                                       	|

## Config

Core manager contains a config file placed under ```config -> core -> manger``` 

to replace the default formatter simple replace the ``` formatter ``` value with an formatter that implements ``` FormatterInterface ```

You can also configure if the transformed data should be wrapped, the following config keys can be set.

* ``` wrap ``` default is ``` data ```
* ``` includes_wrap ``` default is ``` false ```

## Implementation

Its prette easy implenting the core manager. Simply do as following.

Example method in example ```controller```

```php
/**
     * Find all
     * @param ExampleResourceTransformer $resourceTransformer
     * @return JsonResponse|Response
     */
    public function findAll(ExampleResourceTransformer $resourceTransformer)
    {
        $options = $resourceTransformer->parseResourceOptions();
		
        // This is just a proxy to the respository, but needs to be created manually
        $data = $this->exampleService->findAll($options);

        return $resourceTransformer->transformToResponse($data);
    }
```

Thats all. Now every core manager feature can be used.


## Entities

The entity is an abstraction over the standard Laravel model therfore all methods you would normally use, can be used. It is however important not to use the entity as an model because of the options the repository injects on a method call.


#### Creating entities


```php
<?php

namespace {NAMESPACE};

use DeveoDK\Core\Manager\Databases\Entity;

class DummyEntity extends Entity
{
}

```

#### Custom filters

To create a custom filter simply create a method with ``` customFilter```appended to the start.
When the custom filter is created it can be queried through the regular syntax.

```php
public function customFilterPopular($builder, string $operator, string $value, bool $or)
{
    /** @var Builder $builder */
    $builder->where('id', $operator, $value);
}
```

#### Custom sort

To create a custom sort simply create a method with ``` customSort```appended to the start.
When the custom sort is created it can be queried through the regular syntax.

```php
public function customSortPopular($builder, string $direction, string $table)
{
    /** @var Builder $builder */
    $builder->orderBy('id', $direction);
}
```


## Repositories

#### Using repository

The repository includes methods for all CRUD operations even with extensions.

Getting all items in the DB

```php
findAll($resourceParameters);
```

Getting all items in the DB with ``` where ``` statement

```php
findAllWhere($resourceParameters, $attribute, $operator, $value);
```

Getting all items in the DB with ``` whereIn ``` statement

```php
findAllWhereIn($resourceParameters, $attribute, $valueArray);
```

Getting one item from the DB with specific ID

```php
findById($resourceParameters, $id);
```

Getting one item from the DB with ``` where ``` statement

```php
findWhere($resourceParameters, $attribute, $operator, $value);
```

Count all items in the DB

```php
count($resourceParameters);
```

Count all items in the DB with ``` where ``` statement

```php
countWhere($resourceParameters, $attribute, $operator, $value);
```

Sum all items in the DB with attribute

```php
sum($resourceParameters, $field);
```

Sum all items in the DB with ``` where ``` statement

```php
sumWhere($resourceParameters, $field, $attribute, $operator, $value);

```

#### Creating an repository

Your can either use ``` deveodk\core-generator ``` package or simply use the following markup.

```php
<?php

namespace {NAMESPACE};

use DeveoDK\Core\Manager\Repositories\Repository;

class {REPOSITORY} extends Repository
{

    /**
     * Return the Entity the repository should use.
     * @return {ENTITY}
     */
    public function getEntity()
    {
        return new {ENTITY}();
    }
}

```

#### Custom functions

The repository can be extended with custom functions lets asume we need to get all active users.

we can create a function like so

```php

/**
 * Find all active users
 * @return Entity[]
 */ 
public function findAllActiveUsers()
{
	return $this->findAllWhere('active', true);
}

```

To be clear its prefered to user the filters, but this can be convenient when using more advanced find methods. 


#### Securing entity data

To secure an reposiotiry for an authed user we can call the authorization method

```php

/**
 * Construct the repository and apply authorization
 */
public function __construct()
{
	// Code to get user this is just an example
	$user = user();
    $userId = $user->getAttribute('id');
    
	$this->applyAuthorization('user_id', $userId);
}

```

## Resource Transformers

Resource transformers are one of the most important parts of core manager.
With a resource transformer we create a transformation layer for complex data output. We can think of the transformer as the "gap" between your database and your endpoints.

#### Creating resource transformers

```php

<?php

namespace {NAMESPACE};

use DeveoDK\Core\Manager\Transformers\ResourceTransformer;

class {CLASSNAME} extends ResourceTransformer
{
    /**
     * @param $data
     * @return array
     */
    protected function resourceData($data)
    {
        return [
            'id' => $data->getAttribute('id')
        ];
    }

	// This method is optional
    public function extra()
    {
        return [];
    }

	// This method is optional
    public function meta()
    {
        return [];
    }
}

```

#### Handling includes

When dealing with includes(relations) the resourceTransformer class provides an easy API for accessing.

Simply use ``` includes ``` method with name of the ``` entity ``` relation and the ``` class path ``` to the resourceTranformer for the given relation.

```php
$this->includes('relationName', ResourceTransformer::class)
```

#### Using aliases

When using resourceTransformers we are coupling the class pretty thight with the database fields. 

Even through your give a different name for a field than the database have. The resource transformer therefore provide an option to alias both includes and fields. The alias will translate to the database field, so that the internal builder will query the right fields from the DB.

```php
    /** @var array */
    protected $fieldAliases = [
    	'id' => 'user_id'
   	];

    /** @var array */
    protected $includesAlias = [
    	'goodCustomers' => 'badCustomers'
    ];
```


#### Conditional output

To help prevent creating more than one resourceTransformer per entity, an resource transformer provides conditional outputs. 

##### when
When provides a convenient way of conditionally use one given value. If condition fails the array key will be hidden.

```php

protected function resourceData($data)
{
  return [
    'id' => $data->getAttribute('id'),
    'user_id' => $this->when({condition}, $data->getAttribute('user_id')), 
  ];
}

```

##### mergeWhen
MergeWhen provides a convenient way of conditionally use a given arrray. If condition fails the array key will be hidden.

```php

protected function resourceData($data)
{
  return [
    'id' => $data->getAttribute('id'),
    'user_details' => $this->when({condition}, [
    	'id' => $data->getAttribute('user_id'),
        'name' => $data->getAttribute('name')
    ]), 
  ];
}

```

## Manager parameters & Querying 


Core manager support the following query string parameters, we will go through them. one by one. But here is a quick overview.


| NAME     	| DESCRIPTION                                                               	| QUERY STRING 	| DEFAULT              	| ACCEPTS ARRAY 	|   	|
|----------	|---------------------------------------------------------------------------	|--------------	|----------------------	|---------------	|---	|
| Field    	| Include only specified fields                                         	| "fields"     	| All available fields 	| YES           	|   	|
| Format   	| Format the response to either XML, JSON or YAML                           	| "format"     	| JSON                 	| NO            	|   	|
| Limit    	| Limit the outputtet resources to the given number                         	| "limit"      	| 100            	| NO            	|   	|
| Page     	| Automaticly paginates the response and gives HATEOS links for navigation  	| "page"       	| No pagination        	| NO            	|   	|
| Sort     	| Sort values by given parameters                                           	| "sorts"       | No sort              	| YES           	|   	|
| Includes 	| Includes an specified relationship                                        	| "includes"   	| No includes          	| YES           	|   	|
| Filters  	| Query the api with specified queries to get only the information you need 	| "filters"    	| No filters           	| YES           	|   	|

#### Fields querying

Core manager support database level filtering of fields to be transformed. 

What that means is that we can query only the fields that we need. This is to optimize performance, and readability.

Lets assume we only need the ``` id ``` and ``` created_at ``` attribute of our entity. 

Then we can append a query string like ``` ?fields=id,created_at``` 

This will render an result that only has the keys ``` id ``` and ``` created_at ``` 

The only exception to this is if your including data. The relation will always be included. 


#### Format querying

Core manager has out of the box formatting capabilities for ``` JSON ```, ``` XML ``` & ``` YAML ```

| This can be extended through config options.


To format the response to one of the given formats simply query like so

``` ?format=xml ```

This will return an ``` XML ``` response.


#### Limit querying

Core manager support limiting the data that are returned. 

| Default the limit is ``` 100 ``` this can be changed in config



Limit by ``` ?limit=19```


#### Page querying

Core manager supports pagination of an given resource. 

To paginate simply pass the page number you wish to be at, a good ideá is starting at 1 to get meta information for the rest of the pagination. 

``` ?page=1 ```


#### Include querying

To include 




---

[![Deveo footer](https://s3-eu-west-1.amazonaws.com/rk-solutions/github_footer.png)](https://deveo.dk)