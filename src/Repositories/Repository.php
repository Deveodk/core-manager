<?php

namespace DeveoDK\Core\Manager\Repositories;

use DeveoDK\Core\Manager\Databases\ElequentBuilder;
use DeveoDK\Core\Manager\Databases\Entity;
use Illuminate\Database\Eloquent\Builder;
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
        $this->elequentBuilder = app(ElequentBuilder::class);
    }

    /**
     * Return the Entity the repository should use.
     * @return Entity
     */
    abstract public function getEntity();

    /**
     * @param $id
     * @param array $options
     * @return Entity|null
     */
    public function findById($options, $id)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        return $builder->find($id);
    }

    /**
     * @param array $options
     * @return Entity[]|null
     */
    public function findAll($options)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        if (isset($options['page'])) {
            return $builder->paginate($options['limit'], null, 'page', $options['page']);
        }

        return $builder->get();
    }

    /**
     * @param $options
     * @param string $attribute
     * @param string|null $operator
     * @param string|null $value
     * @return Entity[]|null
     */
    public function findAllWhere($options, string $attribute, string $operator = null, string $value = null)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        if (isset($options['page'])) {
            return $builder
                ->where($attribute, $operator, $value)
                ->paginate($options['limit'], null, 'page', $options['page']);
        }

        return $builder->where($attribute, $operator, $value)->get();
    }

    /**
     * @param $options
     * @param $attribute
     * @param array $values
     * @return Model[]|null
     */
    public function findAllWhereIn($options, string $attribute, array $values)
    {
        $builder = $this->elequentBuilder->buildResourceOptions($this->getQueryBuilder(), $options);

        if (isset($options['page'])) {
            return $builder
                ->whereIn($attribute, $values)
                ->paginate($options['limit'], null, 'page', $options['page']);
        }

        return $builder->whereIn($attribute, $values)->get();
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

        return $builder->where($attribute, $value)->first();
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
     * @param $attribute
     * @param $value
     * @return Builder
     */
    public function applyAuthorization($attribute, $value)
    {
        return $this->authorization = $this->getQueryBuilder()->where($attribute, $value);
    }
}
