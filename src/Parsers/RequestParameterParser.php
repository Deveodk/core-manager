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
            'sorts' => null,
            'limit' => null,
            'page' => null,
            'filters' => null,
            'fields' => null,
            'format' => null,
        ], $this->defaults);

        $options = [
            'includes' => $request->get('includes') ? trim($request->get('includes')) : $this->defaults['includes'],
            'sorts' => $request->get('sorts') ? trim($request->get('sorts')) : $this->defaults['sorts'],
            'limit' => $request->get('limit') ? trim($request->get('limit')) : $this->defaults['limit'],
            'page' => $request->get('page') ? trim($request->get('page')) : $this->defaults['page'],
            'filters' => $request->get('filters') ? trim($request->get('filters')) : $this->defaults['filters'],
            'fields' => $request->get('fields') ? trim($request->get('fields')) : $this->defaults['fields'],
            'format' => $request->get('format') ? trim($request->get('format')) : $this->defaults['format'],
        ];

        $includes = $this->parseIncludes($options['includes']);

        $sorts = $this->parseSorts($options['sorts']);

        $limit = $this->parseLimit($options['limit']);

        $page = $this->parsePage($options['page']);

        $fields = $this->parseFields($options['fields']);

        $format = $this->parseFormat($options['format']);

        $filters = $this->parseFilters($options['filters']);

        $this->options = [
            'includes' => $includes,
            'sorts' => $sorts,
            'limit' => $limit,
            'page' => $page,
            'fields' => $fields,
            'format' => $format,
            'filters' => $filters,
        ];

        return $this->options;
    }

    /**
     * Parse includes into array
     * @param $includes
     * @return array|null
     */
    protected function parseIncludes($includes)
    {
        if (is_null($includes)) {
            return null;
        }

        $rawIncludes = explode(',', $includes);

        $includes = [];

        foreach ($rawIncludes as $include) {
            if (empty($include)) {
                continue;
            }

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
    protected function parseSorts($sort)
    {
        if (is_null($sort)) {
            return null;
        }

        $rawSorts = explode(',', $sort);

        $sortsArray = [];

        foreach ($rawSorts as $sorting) {
            $rawSort = explode(':', $sorting);

            if (!isset($rawSort[0])) {
                continue;
            }

            $column = $rawSort[0];
            $direction = 'ASC';
            $table = null;

            // Direction isset
            if (isset($rawSort[1])) {
                $direction = mb_strtolower($rawSort[1]) === 'asc' ? 'ASC' : 'DESC';
            }

            // Table is set
            if (isset($rawSort[2])) {
                $table = mb_strtolower($rawSort[2]);
            }

            array_push($sortsArray, [
                'column' => $column,
                'direction' => $direction,
                'table' => $table,
            ]);
        }

        return $sortsArray;
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

        return (int) $limit;
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

        return (int) $page;
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
            if (empty($field)) {
                continue;
            }

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

            // If array of values given
            if (str_contains($value, '|')) {
                $value = explode('|', $value);
            }

            // Handle bool values
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
            case 'yml':
                return 'yaml';
            default:
                return 'json';
        }
    }
}
