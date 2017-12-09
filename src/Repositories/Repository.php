<?php

namespace DeveoDK\Core\Manager\Repositories;

use Carbon\Carbon;
use DeveoDK\Core\Manager\Databases\ElequentBuilder;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Cache\Repository as cacheRepository;

abstract class Repository
{
    /** @var string */
    const CACHE_PREFIX = 'CORE_MANAGER_COLUMNS_';

    /** @var Model */
    protected $entity;

    /** @var Builder */
    protected $authorization;

    /** @var ElequentBuilder */
    protected $elequentBuilder;

    /** @var DatabaseManager */
    protected $databaseManager;

    /** @var cacheRepository */
    protected $cache;

    /**
     * Repository constructor.
     */
    public function __construct()
    {
        $this->entity = $this->getEntity();
        $this->elequentBuilder = app(ElequentBuilder::class);
        $this->databaseManager = app(DatabaseManager::class);
        $this->cache = app(cacheRepository::class);
    }

    /**
     * @param $id
     * @param array $options
     * @return Model|null
     */
    public function findById($options, $id)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        $fields = null;

        if (isset($options['fields'])) {
            $fields = $this->parseColumnsToInclude($options['fields']);
        }

        return $builder->find($id, $fields);
    }

    /**
     * @param array $options
     * @return Model[]|null
     */
    public function findAll($options)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        $fields = null;

        if (isset($options['fields'])) {
            $fields = $this->parseColumnsToInclude($options['fields']);
        }

        if (isset($options['page'])) {
            return $builder->paginate($options['limit'], $fields, 'page', $options['page']);
        }

        return $builder->get($fields);
    }

    /**
     * @param $options
     * @param $attribute
     * @param null $operator
     * @param null $value
     * @return Model[]|null
     */
    public function findAllWhere($options, $attribute, $operator = null, $value = null)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        $fields = null;

        if (isset($options['fields'])) {
            $fields = $this->parseColumnsToInclude($options['fields']);
        }

        return $builder->where($attribute, $operator, $value)->get($fields);
    }

    /**
     * @param $options
     * @param $attribute
     * @param array $values
     * @return Model[]|null
     */
    public function findAllWhereIn($options, $attribute, array $values)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        $fields = null;

        if (isset($options['fields'])) {
            $fields = $this->parseColumnsToInclude($options['fields']);
        }

        return $builder->whereIn($attribute, $values)->get($fields);
    }

    /**
     * Count all
     * @param $options
     * @return int
     */
    public function count($options)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        return $builder->count();
    }

    /**
     * @param $options
     * @param $attribute
     * @return int
     */
    public function sum($options, $attribute)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        return $builder->sum($attribute);
    }

    /**
     * @param $options
     * @param $attribute
     * @param $value
     * @return Model|null
     */
    public function findBy($options, $attribute, $value)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        $fields = null;

        if (isset($options['fields'])) {
            $fields = $this->parseColumnsToInclude($options['fields']);
        }

        return $builder->where($attribute, $value)->first($fields);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function save(Model $entity)
    {
        return $entity->save();
    }

    /**
     * @param $value
     * @param string $attribute
     * @return bool
     */
    public function delete($value, $attribute = 'id')
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->where($attribute, $value)->delete();
    }

    /**
     * Get query builder
     * @return Builder
     */
    public function getQueryBuilder()
    {
        if ($this->authorization) {
            return $this->authorization;
        }

        return $this->entity->newQuery();
    }

    /**
     * @param array|null $fields
     * @return array
     */
    public function parseColumnsToInclude($fields)
    {
        if (is_null($fields)) {
            return $fields;
        }

        $tableName = $this->getEntity()->getTable();
        $columns = $this->getDatabaseColumns($tableName);

        $columnsToInclude = [];

        // field aliases all ready applied from parser
        foreach ($fields as $field) {
            if (in_array($field, $columns)) {
                array_push($columnsToInclude, $field);
            }
        }

        if (count($columnsToInclude) === 0) {
            return null;
        }

        return $columnsToInclude;
    }

    /**
     * @param $tableName
     * @return array
     */
    protected function getDatabaseColumns($tableName)
    {
        $timeToRemember = Carbon::now()->addHour();

        $columns = $this->cache
            ->remember(self::CACHE_PREFIX . $tableName, $timeToRemember, function () use ($tableName) {
                return $this->databaseManager->getSchemaBuilder()->getColumnListing($tableName);
            });

        return $columns;
    }

    /**
     * @param $attribute
     * @param $value
     * @return Builder
     */
    public function applyAuthorization($attribute, $value)
    {
        return $this->authorization = $this->getQueryBuilder()->where($attribute, $value);
    }
}
