<?php

namespace DeveoDK\Core\Manager\Transformers;

use DeveoDK\Core\Manager\Resources\EmptyRelation;
use DeveoDK\Core\Manager\Resources\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\MissingValue;

trait RelationsTransformer
{
    /**
     * @param string $relationName
     * @param string $transformer
     * @return mixed|null
     */
    protected function includes($relationName, $transformer)
    {
        /** @var Model $model */
        $model = $this->data;

        // When relation does not exist
        if (!$this->relationExist($model, $relationName)) {
            return new MissingValue();
        }

        $data = $model->{$relationName};

        if (is_null($data)) {
            return new EmptyRelation();
        }

        // If relation is empty
        if (empty($data) === 0) {
            return new EmptyRelation();
        }

        /** @var ResourceTransformer $resourceTransformer */
        $resourceTransformer = new $transformer;

        $transformed = $resourceTransformer->transform($data);

        return new Relation($transformed);
    }

    /**
     * Determine if the relation exist on this instance
     * @param Model $model
     * @param string $relationName
     * @return bool
     */
    private function relationExist($model, $relationName)
    {
        return array_key_exists($relationName, $model->getRelations());
    }
}
