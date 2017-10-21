<?php

namespace DeveoDK\Core\Manager\Databases;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElequentBuilder
{
    /** @var Builder */
    protected $queryBuilder;

    /**
     * @param Builder $queryBuilder
     * @param array $options
     * @return Builder
     */
    public function buildResourceOptions(Builder $queryBuilder, array $options = [])
    {
        $this->queryBuilder = $queryBuilder;

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
