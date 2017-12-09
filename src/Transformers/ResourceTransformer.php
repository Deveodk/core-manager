<?php

namespace DeveoDK\Core\Manager\Transformers;

use DeveoDK\Core\Manager\Parsers\RequestParameterParser;
use DeveoDK\Core\Manager\Resources\EmptyRelation;
use DeveoDK\Core\Manager\Resources\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class ResourceTransformer
{
    // Parse options from request
    use RequestParameterParser, RelationsTransformer;

    /** @var Formatter */
    protected $formatter;

    /** @var Request */
    protected $request;

    /** @var array */
    protected $fieldAliases = [];

    /** @var array */
    protected $includesAlias = [];

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $data = [];

    /**
     * ResourceTransformer constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        $this->formatter = app(Formatter::class);
        $this->request = app(Request::class);
        $this->options = $options;
    }

    /**
     * @param $data
     * @return array
     */
    public function transform($data)
    {
        $this->formatter->setMeta($this->getMeta());
        $this->formatter->setWith($this->getExtra());

        if ($data instanceof LengthAwarePaginator) {
            $this->withPaginator($data);
        }

        if ($data instanceof Collection || $data instanceof LengthAwarePaginator) {
            $transformed = [];

            foreach ($data as $transformable) {
                // Save in temp var for relations
                $this->data = $transformable;
                $array = $this->resourceData($transformable);

                array_push($transformed, $array);
            }

            return $this->filter($transformed);
        }

        if (is_null($data)) {
            return [];
        }

        // Save in temp var for relations
        $this->data = $data;
        $array = $this->resourceData($data);

        return $this->filter($array);
    }

    /**
     * @param $data
     * @param int $status
     * @return JsonResponse|Response
     */
    public function transformToResponse($data, int $status = 200)
    {
        return $this->toResponse($this->transform($data), $status);
    }

    /**
     * @param LengthAwarePaginator $lengthAwarePaginator
     */
    protected function withPaginator(LengthAwarePaginator $lengthAwarePaginator)
    {
        $paginator = $lengthAwarePaginator->toArray();

        $meta = [
            'current_page' => $paginator['current_page'],
            'from' => $paginator['from'],
            'last_page' => $paginator['last_page'],
            'path' => $paginator['path'],
            'per_page' => (int) $paginator['per_page'],
            'to' => $paginator['to'],
            'total' => $paginator['total']
        ];
        $links = [
            'links' => [
                'first' => $this->getCorrectPaginatorLink($paginator['first_page_url']),
                'last' => $this->getCorrectPaginatorLink($paginator['last_page_url']),
                'prev' => $this->getCorrectPaginatorLink($paginator['prev_page_url']),
                'next' => $this->getCorrectPaginatorLink($paginator['next_page_url'])
            ],
        ];

        $this->formatter->setMeta($meta);
        $this->formatter->setWith($links);
    }

    /**
     * @param $url
     * @return string
     */
    protected function getCorrectPaginatorLink($url)
    {
        if (is_null($url)) {
            return $url;
        }

        $rawQueryString = $this->request->query();
        unset($rawQueryString['page']);

        $queryString = http_build_query($rawQueryString);

        return sprintf('%s&%s', $url, $queryString);
    }

    /**
     * @param $data
     * @param int $status
     * @return JsonResponse|Response
     */
    protected function toResponse($data, $status = 200)
    {
        $formatter = $this->formatter;

        switch ($this->getOptions()['format']) {
            case 'xml':
                return $formatter->toXML($data, $status);
            case 'yaml':
                return $formatter->toYaml($data, $status);
            case 'yml':
                return $formatter->toYaml($data, $status);
            case 'json':
                return $formatter->toJson($data, $status);
            default:
                return $formatter->toJson($data, $status);
        }
    }

    /**
     * @param $array
     * @return array
     */
    protected function filter($array)
    {
        $fieldsSelected = isset($this->getOptions()['fields']) ? $this->getOptions()['fields'] : null;

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
                $mergeData = $value->data;
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
     * @return array
     */
    public function getExtra()
    {
        if (method_exists($this, 'extra')) {
            return $this->extra();
        }

        return [];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        if (method_exists($this, 'meta')) {
            return $this->meta();
        }

        return [];
    }

    /**
     * Merge a value based on a given condition.
     *
     * @param  bool  $condition
     * @param  mixed  $value
     * @return \Illuminate\Http\Resources\MissingValue|mixed
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
