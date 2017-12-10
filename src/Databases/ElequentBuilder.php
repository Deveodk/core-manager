<?php

namespace DeveoDK\Core\Manager\Databases;

use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Cache\Repository as CacheRepository;

class ElequentBuilder
{
    /** @var string */
    const CACHE_PREFIX = 'CORE_MANAGER_COLUMNS_';

    /** @var Builder */
    protected $queryBuilder;

    /** @var CacheRepository */
    protected $cache;

    /** @var DatabaseManager */
    protected $databaseManager;

    public function __construct()
    {
        $this->cache = app(CacheRepository::class);
        $this->databaseManager = app(DatabaseManager::class);
    }

    /**
     * @param Builder $queryBuilder
     * @param array $options
     * @return Builder
     */
    public function buildResourceOptions(Builder $queryBuilder, array $options = [])
    {
        $this->queryBuilder = $queryBuilder;

        // Set includes default value
        $includes = null;

        // Extract array into variables
        extract($options);

        if (isset($includes)) {
            $this->parseIncludes($includes);
        }

        if (isset($sort)) {
            $this->parseSort($sort);
        }

        if (isset($limit)) {
            $this->parseLimit($limit);
        }

        if (isset($fields)) {
            $this->parseFields($fields, $includes);
        }

        if (isset($filters)) {
            $this->parseFilters($filters);
        }

        return $this->getQueryBuilder();
    }

    /**
     * @param array $includes
     * @return void
     */
    protected function parseIncludes(array $includes)
    {
        $model = $this->getQueryBuilder()->getModel();

        $included = [];

        foreach ($includes as $include) {
            if (method_exists($model, $include)) {
                array_push($included, $include);
            }
        }

        $this->getQueryBuilder()->with($included);
    }

    /**
     * @param array $sort
     * @return Builder
     */
    protected function parseSort(array $sort)
    {
        $model = $this->getQueryBuilder()->getModel();
        $queryBuilder = $this->getQueryBuilder();

        $joined = [];

        foreach ($sort as $sorting) {
            $table = ($sorting['table']) ? $sorting['table'] : $model->getTable();
            $column = $sorting['column'];
            $direction = $sorting['direction'];

            // If the relation has been joined
            if (array_key_exists($table, $joined)) {
                $this->orderBy($joined[$table], $column, $direction);
                continue;
            }

            // Does relationship exist?
            if (method_exists($model, $table)) {
                /** @var HasMany|BelongsTo|BelongsToMany $relation */
                $relation = $model->$table();

                if ($relation instanceof HasMany) {
                    $joined = array_add($joined, $table, $relation->getRelated()->getTable());
                    $queryBuilder->leftJoin(
                        $relation->getRelated()->getTable(),
                        $relation->getQualifiedParentKeyName(),
                        '=',
                        $relation->getQualifiedForeignKeyName()
                    );

                    $this->orderBy($relation->getRelated()->getTable(), $column, $direction);
                } elseif ($relation instanceof BelongsToMany) {
                    $queryBuilder->leftJoin(
                        $relation->getTable(),
                        $relation->getQualifiedParentKeyName(),
                        '=',
                        $relation->getQualifiedForeignKeyName()
                    );
                    $queryBuilder->leftJoin(
                        $relation->getRelated()->getTable(),
                        $relation->getRelated()->getTable(). '.' . $relation->getRelated()->getKeyName(),
                        '=',
                        $relation->getQualifiedRelatedPivotKeyName()
                    );

                    $this->orderBy($relation->getRelated()->getTable(), $column, $direction);
                }

                continue;
            }

            $this->orderBy($model->getTable(), $column, $direction);
        }

        return $queryBuilder;
    }

    /**
     * @param $fields
     * @param $includes
     * @return Builder|null
     */
    protected function parseFields($fields, $includes)
    {
        if (is_null($fields)) {
            return $fields;
        }

        $queryBuilder = $this->getQueryBuilder();
        $tableName = $queryBuilder->getModel()->getTable();
        $columns = $this->getDatabaseColumns($tableName);

        $columnsToInclude = [];

        // field aliases all ready applied from parser
        foreach ($fields as $field) {
            if (in_array($field, $columns)) {
                array_push($columnsToInclude, $field);
            }
        }

        if ($includes) {
            // When includes then select the column manually
            foreach ($includes as $include) {
                foreach ($columns as $column) {
                    if (str_contains($column, $include)) {
                        array_push($columnsToInclude, $column);
                    }
                }
            }
        }

        if (count($columnsToInclude) === 0) {
            return null;
        }

        return $queryBuilder->select($columnsToInclude);
    }

    /**
     * @param $filters
     * @return Builder|null
     */
    protected function parseFilters($filters)
    {
        $queryBuilder = $this->getQueryBuilder();
        $tableName = $queryBuilder->getModel()->getTable();

        if (is_null($filters)) {
            return null;
        }

        foreach ($filters as $filter) {
            $field = $filter['field'];
            $operator = $filter['operator'];
            $value = $filter['value'];

            $columns = $this->getDatabaseColumns($tableName);

            if (!in_array($field, $columns)) {
                continue;
            }

            $whereOperators = ['=', '!=', '<', '>', '>=', '<=', '<>', 'like'];

            if (in_array($operator, $whereOperators)) {
                $queryBuilder->where($field, $operator, $value);
                continue;
            }

            switch ($operator) {
                case 'between':
                    $queryBuilder->whereBetween($field, $value);
                    break;
                case 'not_between':
                    $queryBuilder->whereNotBetween($field, $value);
                    break;
                case 'in':
                    $queryBuilder->whereIn($field, $value);
                    break;
                case 'not_in':
                    $queryBuilder->whereNotIn($field, $value);
                    break;
                case 'month':
                    $queryBuilder->whereMonth($field, $value);
                    break;
                case 'day':
                    $queryBuilder->whereDay($field, $value);
                    break;
                case 'date':
                    $queryBuilder->whereDate($field, $value);
                    break;
                case 'year':
                    $queryBuilder->whereYear($field, $value);
                    break;
                case 'time':
                    $queryBuilder->whereTime($field, '=', $value);
                    break;
            }
        }

        return $queryBuilder;
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
     * @param int $limit
     */
    protected function parseLimit(int $limit)
    {
        $this->getQueryBuilder()->limit($limit);
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $direction
     */
    protected function orderBy(string $table, string $column, string $direction)
    {
        $this->getQueryBuilder()->orderBy(
            sprintf(
                '%s.%s',
                $table,
                $column
            ),
            $direction
        );
    }

    /**
     * @return Builder
     */
    protected function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
