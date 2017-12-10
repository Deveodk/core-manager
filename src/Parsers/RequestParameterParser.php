<?php

namespace DeveoDK\Core\Manager\Parsers;

use Illuminate\Http\Request;

trait RequestParameterParser
{
    /** @var array */
    protected $defaults = [];

    /** @var array */
    protected $options = [];

    /** @var Request */
    protected $request;

    /**
     * RequestParameterParser constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function parseResourceOptions()
    {
        $request = $this->request;

        $this->defaults = array_merge([
            'includes' => null,
            'sort' => null,
            'limit' => null,
            'page' => null,
            'filters' => null,
            'fields' => null,
            'format' => null,
        ], $this->defaults);

        $options = [
            'includes' => $request->get('includes') ? trim($request->get('includes')) : $this->defaults['includes'],
            'sort' => $request->get('sort') ? trim($request->get('sort')) : $this->defaults['sort'],
            'limit' => $request->get('limit') ? trim($request->get('limit')) : $this->defaults['limit'],
            'page' => $request->get('page') ? trim($request->get('page')) : $this->defaults['page'],
            'filters' => $request->get('filters') ? trim($request->get('filters')) : $this->defaults['filters'],
            'fields' => $request->get('fields') ? trim($request->get('fields')) : $this->defaults['fields'],
            'format' => $request->get('format') ? trim($request->get('format')) : $this->defaults['format'],
        ];

        $includes = $this->parseIncludes($options['includes']);

        $sort = $this->parseSort($options['sort']);

        $limit = $this->parseLimit($options['limit']);

        $page = $this->parsePage($options['page']);

        $fields = $this->parseFields($options['fields']);

        $format = $this->parseFormat($options['format']);

        $filters = $this->parseFilters($options['filters']);

        $this->options = [
            'includes' => $includes,
            'sort' => $sort,
            'limit' => $limit,
            'page' => $page,
            'fields' => $fields,
            'format' => $format,
            'filters' => $filters,
        ];

        return $this->options;
    }

    protected function parseIncludes($includes)
    {
        if (is_null($includes)) {
            return null;
        }

        $rawIncludes = explode(',', $includes);

        $includes = [];

        foreach ($rawIncludes as $include) {
            $formattedInclude = camel_case($include);

            // If alias of field
            if (key_exists($formattedInclude, $this->includesAlias)) {
                array_push($includes, $this->includesAlias[$formattedInclude]);
                continue;
            }

            array_push($includes, $formattedInclude);
        }

        return $includes;
    }

    /**
     * @param $sort
     * @return array|null
     */
    protected function parseSort($sort)
    {
        if (is_null($sort)) {
            return null;
        }

        $rawSort = explode(',', $sort);

        $sortArray = [];

        foreach ($rawSort as $sorting) {
            $directionParse = explode(':', $sorting);

            $direction = 'asc';

            $tableColumn = $directionParse[0];

            // If direction is not given default to ascending
            if (count($directionParse) === 2) {
                $direction = $directionParse[1];
            }

            // If non existing order default to desc
            $direction = mb_strtolower($direction) === 'asc' ? 'ASC' : 'DESC';

            $columnParser = explode('.', $tableColumn);

            if (count($columnParser) === 2) {
                $table = $columnParser[0];
                $column = $columnParser[1];
            } else {
                $table = null;
                $column = $columnParser[0];
            }

            array_push($sortArray, [
                'direction' => $direction,
                'table' => $table,
                'column' => $column
            ]);
        }

        return $sortArray;
    }

    /**
     * @param $limit
     * @return null
     */
    protected function parseLimit($limit)
    {
        if (is_null($limit)) {
            return null;
        }

        return $limit;
    }

    /**
     * @param $page
     * @return null|int
     */
    protected function parsePage($page)
    {
        if (is_null($page)) {
            return null;
        }

        return $page;
    }

    /**
     * @param $fields
     * @return array
     */
    protected function parseFields($fields)
    {
        // if no fields given
        if (is_null($fields)) {
            return $fields;
        }

        $rawFields = explode(',', $fields);

        $fields = [];

        foreach ($rawFields as $field) {
            // If alias of field
            if (key_exists($field, $this->fieldAliases)) {
                array_push($fields, $this->fieldAliases[$field]);
                continue;
            }

            array_push($fields, $field);
        }

        // if 0 fields return null
        if (count($fields) === 0) {
            return null;
        }

        return $fields;
    }

    /**
     * @param $filters
     * @return array|null
     */
    protected function parseFilters($filters)
    {
        if (is_null($filters)) {
            return null;
        }

        $rawFilters = explode(',', $filters);

        $filtersArray = [];

        foreach ($rawFilters as $filter) {
            $rawArray = explode(':', $filter);

            if (!is_array($rawArray)) {
                continue;
            }

            if (count($rawArray) !== 3) {
                continue;
            }

            $field = trim($rawArray[0]);
            $operator = trim($rawArray[1]);
            $value = trim($rawArray[2]);

            // Handle boolean values
            if ($value === 'true') {
                $value = true;
            }

            if ($value === 'false') {
                $value = false;
            }

            array_push($filtersArray, [
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ]);
        }

        return $filtersArray;
    }

    /**
     * @param string $format
     * @return string
     */
    protected function parseFormat($format)
    {
        switch (mb_strtolower($format)) {
            case 'xml':
                return 'xml';
            case 'json':
                return 'json';
            case 'yaml':
                return 'yaml';
            default:
                return 'json';
        }
    }
}
