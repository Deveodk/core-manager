<?php

namespace DeveoDK\Core\Manager\Transformers;

use DeveoDK\Core\Manager\Databases\Entity;
use DeveoDK\Core\Manager\Exceptions\FormatterDoNotImplementInterface;
use DeveoDK\Core\Manager\Formatters\Formatter;
use DeveoDK\Core\Manager\Formatters\FormatterInterface;
use DeveoDK\Core\Manager\Paginators\Paginator;
use DeveoDK\Core\Manager\Parsers\RequestParameterParser;
use DeveoDK\Core\Manager\Parsers\RequestParameters;
use DeveoDK\Core\Manager\Resources\EmptyRelation;
use DeveoDK\Core\Manager\Resources\MergeValue;
use DeveoDK\Core\Manager\Resources\MissingValue;
use DeveoDK\Core\Manager\Resources\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class ResourceTransformer
{
    /** @var Request */
    protected $request;

    /** @var array */
    protected $fieldAliases = [];

    /** @var array */
    protected $includesAlias = [];

    /** @var array */
    protected $data = [];

    /** @var RequestParameters */
    protected $requestParameters;

    /** @var string */
    protected $wrap;

    /** @var array */
    private $meta = ['meta' => []];

    /** @var array */
    private $extra = [];

    /**
     * ResourceTransformer constructor.
     * @param Request|null $request
     */
    public function __construct(?Request $request = null)
    {
        $this->request = new Request();

        if ($request) {
            $this->request = $request;
        }

        if (!$this->wrap) {
            $this->wrap = config('core.manager.wrap');
        }

        $this->mergeExtra();
        $this->mergeMeta();
    }

    /**
     * Parse resource options
     *
     * @return RequestParameters
     */
    public function parseResourceOptions()
    {
        $requestParameterParser = new RequestParameterParser(
            $this->request,
            $this->fieldAliases,
            $this->includesAlias
        );

        return $this->requestParameters = $requestParameterParser->parseResourceOptions();
    }

    /**
     * @param $data
     * @param bool $wrap
     * @return array
     */
    public function transform($data, bool $wrap = true)
    {
        if (is_null($data)) {
            return [];
        }

        if ($data instanceof LengthAwarePaginator) {
            $paginator = new Paginator($this->request);
            $paginator->formatPaginator($data);

            $this->meta['meta'] = array_merge($this->meta['meta'], $paginator->getMeta());
            $this->extra = array_merge($this->extra, $paginator->getLinks());
        }

        if ($data instanceof Collection || $data instanceof LengthAwarePaginator || is_array($data)) {
            $transformed = [];

            foreach ($data as $transformable) {
                // Save in temp var for relations
                $this->data = $transformable;
                $array = $this->resourceData($transformable);

                array_push($transformed, $array);
            }

            $data = $this->filter($transformed);

            if ($wrap) {
                $data = $this->wrap($data);
            }

            return $this->mergeMetaAndExtra($data);
        }

        // Save in temp var for relations
        $this->data = $data;
        $array = $this->resourceData($data);

        $data = $this->filter($array);

        if ($wrap) {
            $data = $this->wrap($data);
        }

        return $this->mergeMetaAndExtra($data);
    }

    /**
     * @param $data
     * @param int $status
     * @return JsonResponse|Response
     * @throws FormatterDoNotImplementInterface
     */
    public function transformToResponse($data, int $status = 200)
    {
        return $this->toResponse($this->transform($data), $status);
    }

    /**
     * @param $data
     * @param int $status
     * @return JsonResponse|Response
     * @throws FormatterDoNotImplementInterface
     */
    private function toResponse($data, $status = 200)
    {
        $config = config('core.manager');

        /** @var FormatterInterface $formatter */
        $formatter = isset($config['formatter']) ? $config['formatter'] : Formatter::class;

        $formattersInterface = class_implements($formatter);

        if (!isset($formattersInterface[FormatterInterface::class])) {
            throw new FormatterDoNotImplementInterface();
        }

        $formatter = new $formatter;
        $format = $this->getRequestParameters()->getFormat();

        return $formatter->toResponse($data, $status, $format);
    }

    /**
     * @param array $data
     * @return array
     */
    private function mergeMetaAndExtra(array $data)
    {
        $meta = (empty($this->meta['meta'])) ? [] : $this->meta;

        return array_merge_recursive($data, $meta, $this->extra);
    }

    /**
     * @param $array
     * @return array
     */
    private function filter($array)
    {
        $fieldsSelected = $this->getRequestParameters()->getFields();

        foreach ($array as $key => $value) {
            // Call recurse if array and combine the keys
            if (is_array($value)) {
                $array[$key] = $this->filter($value);
                continue;
            }

            if ($value instanceof Relation) {
                $array[$key] = $value->getData();
                continue;
            }

            if ($value instanceof EmptyRelation) {
                $array[$key] = null;
                continue;
            }

            if ($value instanceof MergeValue) {
                $mergeData = $value->getData();
                $mergeData = $this->filter($mergeData);

                foreach ($mergeData as $index => $data) {
                    $array[$index] = $data;
                }

                unset($array[$key]);
            }

            if ($value instanceof MissingValue) {
                unset($array[$key]);
            }

            if (!is_null($fieldsSelected)) {
                if (!in_array($key, $fieldsSelected)) {
                    unset($array[$key]);
                    continue;
                }
            }
        }

        return $array;
    }

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

        $transformed = $resourceTransformer->transform($data, config('core.manager.includes_wrap'));

        return new Relation($transformed);
    }

    /**
     * Determine if the relation exist on this instance
     * @param Model|Entity $model
     * @param string $relationName
     * @return bool
     */
    private function relationExist($model, $relationName)
    {
        return array_key_exists($relationName, $model->getRelations());
    }

    /**
     * Merge extra into array
     * @return void
     */
    private function mergeExtra()
    {
        if (method_exists($this, 'extra')) {
            $this->extra = $this->extra();
        }
    }

    /**
     * Merge meta data
     * @return void
     */
    private function mergeMeta()
    {
        if (method_exists($this, 'meta')) {
            $this->meta['meta'] = $this->meta();
        }
    }

    /**
     * Merge a value based on a given condition.
     *
     * @param  bool  $condition
     * @param  mixed  $value
     * @return MissingValue|mixed
     */
    protected function mergeWhen($condition, $value)
    {
        return $condition ? new MergeValue(value($value)) : new MissingValue;
    }

    /**
     * Retrieve a value based on a given condition.
     *
     * @param  bool  $condition
     * @param  mixed  $value
     * @param  mixed  $default
     * @return mixed
     */
    protected function when($condition, $value, $default = null)
    {
        if ($condition) {
            return value($value);
        }

        return func_num_args() === 3 ? value($default) : null;
    }

    /**
     * @return RequestParameters
     */
    private function getRequestParameters()
    {
        if ($this->requestParameters) {
            return $this->requestParameters;
        }

        return $this->requestParameters = new RequestParameters();
    }

    /**
     * @param array $data
     * @return array
     */
    private function wrap(array $data)
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        }

        if (is_null($this->wrap)) {
            return $data;
        }

        if ($this->wrap === '') {
            return $data;
        }

        return [$this->wrap => $data];
    }
}
