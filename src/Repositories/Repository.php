<?php

namespace DeveoDK\Core\Manager\Repositories;

use DeveoDK\Core\Manager\Databases\ElequentBuilder;
use DeveoDK\Core\Manager\Databases\Entity;
use DeveoDK\Core\Manager\Parsers\RequestParameters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class Repository
{
    /** @var Entity */
    protected $entity;

    /** @var Builder */
    protected $authorization;

    /** @var ElequentBuilder */
    protected $elequentBuilder;

    /**
     * Repository constructor.
     */
    public function __construct()
    {
        $this->entity = $this->getEntity();
        $this->elequentBuilder = new ElequentBuilder();
    }

    /**
     * Return the Entity the repository should use.
     * @return Entity
     */
    abstract public function getEntity();

    /**
     * @param RequestParameters $parameters
     * @param $id
     * @return Entity|Model|null
     */
    public function findById(RequestParameters $parameters, $id)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        return $builder->find($id);
    }

    /**
     * @param RequestParameters $parameters
     * @return LengthAwarePaginator|Collection
     */
    public function findAll(RequestParameters $parameters)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        if ($parameters->getPage()) {
            return $builder->paginate($parameters->getLimit(), null, 'page', $parameters->getPage());
        }

        return $builder->get();
    }

    /**
     * @param RequestParameters $parameters
     * @param string $attribute
     * @param string|null $operator
     * @param string|null $value
     * @return LengthAwarePaginator|Collection
     */
    public function findAllWhere(
        RequestParameters $parameters,
        string $attribute,
        string $operator = null,
        string $value = null
    ) {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        if (isset($options['page'])) {
            return $builder
                ->where($attribute, $operator, $value)
                ->paginate($parameters->getLimit(), null, 'page', $parameters->getPage());
        }

        return $builder->where($attribute, $operator, $value)->get();
    }

    /**
     * @param RequestParameters $parameters
     * @param $attribute
     * @param array $values
     * @return LengthAwarePaginator|Collection
     */
    public function findAllWhereIn(RequestParameters $parameters, $attribute, array $values)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        if (isset($options['page'])) {
            return $builder
                ->whereIn($attribute, $values)
                ->paginate($parameters->getLimit(), null, 'page', $parameters->getPage());
        }

        return $builder->whereIn($attribute, $values)->get();
    }

    /**
     * Count all
     * @param RequestParameters $parameters
     * @return int
     */
    public function count(RequestParameters $parameters)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        return $builder->count();
    }

    /**
     * @param RequestParameters $parameters
     * @param $attribute
     * @param $operator
     * @param $value
     * @return int
     */
    public function countWhere(RequestParameters $parameters, $attribute, $operator, $value)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        return $builder->where($attribute, $operator, $value)->count();
    }

    /**
     * @param RequestParameters $parameters
     * @param $field
     * @return int
     */
    public function sum(RequestParameters $parameters, $field)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        return $builder->sum($field);
    }

    /**
     * @param RequestParameters $parameters
     * @param $field
     * @param $attribute
     * @param $operator
     * @param $value
     * @return int
     */
    public function sumWhere(RequestParameters $parameters, $field, $attribute, $operator, $value)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        return $builder->where($attribute, $operator, $value)->sum($field);
    }

    /**
     * @param RequestParameters $parameters
     * @param $attribute
     * @param $operator
     * @param $value
     * @return Entity|Model|null
     */
    public function findBy(RequestParameters $parameters, $attribute, $operator, $value)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $parameters);

        return $builder->where($attribute, $operator, $value)->first();
    }

    /**
     * Create the entity and return the created instance
     * @param Entity $entity
     * @return Entity|Model
     */
    public function create(Entity $entity)
    {
        $entity->save();
        return $this->getQueryBuilder()->find($entity->getAttribute('id'));
    }

    /**
     * Update the entity and return the updated instance
     * @param Entity $entity
     * @return Entity
     */
    public function update(Entity $entity)
    {
        $entity->save();
        return $entity;
    }

    /**
     * @param Entity $entity
     * @return Entity|Model
     * @throws \Exception
     */
    public function delete(Entity $entity)
    {
        $entity->delete();

        return $entity;
    }

    /**
     * Get query builder instance
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
     * @param $attribute
     * @param $value
     * @return Builder
     */
    public function applyAuthorization($attribute, $value)
    {
        return $this->authorization = $this->getQueryBuilder()->where($attribute, $value);
    }
}
