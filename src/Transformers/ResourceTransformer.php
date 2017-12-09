<?php

namespace DeveoDK\Core\Manager\Transformers;

use DeveoDK\Core\Manager\Parsers\RequestParameterParser;
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
     * @return mixed
     */
    public function transform($data)
    {
        $this->formatter->setMeta($this->getMeta());
        $this->formatter->setWith($this->getExtra());

        if ($data instanceof LengthAwarePaginator) {
            $this->withPaginator($data);
        }

        $transformed = [];

        if ($data instanceof Collection || $data instanceof LengthAwarePaginator) {
            foreach ($data as $transformable) {
                // Save in temp var for relations
                $this->data = $transformable;
                $array = $this->resourceData($transformable);

                array_push($transformed, $array);
            }
        } elseif (is_null($data)) {
            // Do nothing
        } else {
            // Save in temp var for relations
            $this->data = $data;
            $array = $this->resourceData($data);

            $transformed = $array;
        }

        $data = $this->removeUnselectedFields($transformed);

        return $this->filter($data);
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
            case 'json':
                return $formatter->toJson($data, $status);
            default:
                return $formatter->toJson($data, $status);
        }
    }

    /**
     * Filter the given data, removing any optional values.
     *
     * @param  array  $data
     * @return array
     */
    protected function filter($data)
    {
        $index = -1;

        foreach ($data as $key => $value) {
            $index++;

            if ($value instanceof Relation) {
                $data[$key] = $value->getData();
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->filter($value);

                continue;
            }

            if (is_numeric($key) && $value instanceof MergeValue) {
                return $this->merge($data, $index, $this->filter($value->data));
            }

            if ($value instanceof MissingValue ||
                ($value instanceof self &&
                    $value->resource instanceof MissingValue)) {
                unset($data[$key]);

                $index--;
            }

            if ($value instanceof self && is_null($value->resource)) {
                $data[$key] = null;
            }
        }

        return $data;
    }

    /**
     * Merge the given data in at the given index.
     *
     * @param  array  $data
     * @param  int  $index
     * @param  array  $merge
     * @return array
     */
    protected function merge($data, $index, $merge)
    {
        if (array_values($data) === $data) {
            return array_merge(
                array_merge(array_slice($data, 0, $index, true), $merge),
                $this->filter(array_slice($data, $index + 1, null, true))
            );
        }

        return array_slice($data, 0, $index, true) +
            $merge +
            $this->filter(array_slice($data, $index + 1, null, true));
    }

    /**
     * @param $array
     * @return mixed
     */
    protected function removeUnselectedFields($array)
    {
        $fieldsSelected = isset($this->getOptions()['fields']) ? $this->getOptions()['fields'] : null;

        if ($fieldsSelected === null) {
            return $array;
        }

        if (count($array) >= 2) {
            foreach ($array as $i => $transformable) {
                foreach ($transformable as $key => $value) {
                    if ($value instanceof Relation) {
                        $transformable[$key] = $value->getData();
                        continue;
                    }

                    if (in_array($key, $fieldsSelected)) {
                        continue;
                    }

                    unset($transformable[$key]);
                }

                // Set current array item to filtered
                $array[$i] = $transformable;
            }

            return $array;
        }

        foreach ($array as $key => $value) {
            if ($value instanceof Relation) {
                $array[$key] = $value->getData();
                continue;
            }

            if (in_array($key, $fieldsSelected)) {
                continue;
            }

            unset($array[$key]);
        }


        return $array;
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
