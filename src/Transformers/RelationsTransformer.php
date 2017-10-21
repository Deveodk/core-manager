<?php

namespace DeveoDK\Core\Manager\Transformers;

use DeveoDK\Core\Manager\Resources\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\MissingValue;

trait RelationsTransformer
{
    /**
     * @param string $resourceName
     * @param string $transformer
     * @return mixed|null
     */
    protected function includes($resourceName, $transformer)
    {
        /** @var Model $model */
        $model = $this->data;

        // When relation does not exist
        if (!$this->relationExist($model, $resourceName)) {
            return new MissingValue();
        }

        $data = $model->{$resourceName};

        // If relation is empty
        if (count($data) === 0) {
            return null;
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
        return isset($model->getRelations()[$relationName]);
    }
}
