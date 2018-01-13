<?php

namespace DeveoDK\Core\Manager\Parsers;

use Illuminate\Http\Request;

class RequestParameterParser
{
    /** @var Request */
    protected $request;

    /** @var array */
    protected $fieldAliases = [];

    /** @var array */
    protected $includesAlias = [];

    /** @var array */
    protected $includes;

    /** @var array */
    protected $sorts;

    /** @var int|null */
    protected $limit;

    /** @var int|null */
    protected $page;

    /** @var array */
    protected $filters;

    /** @var array */
    protected $fields;

    /** @var string */
    protected $format;

    /**
     * RequestParameterParser constructor.
     * @param Request $request
     * @param array $fieldAliases
     * @param array $includesAlias
     */
    public function __construct(Request $request, array $fieldAliases, array $includesAlias)
    {
        $this->request = $request;
        $this->fieldAliases = $fieldAliases;
        $this->includesAlias = $includesAlias;
    }

    /**
     * @return RequestParameters
     */
    public function parseResourceOptions()
    {
        $request = $this->request;

        $options = [
            'includes' => $request->get('includes') ? trim($request->get('includes')) : null,
            'sorts' => $request->get('sorts') ? trim($request->get('sorts')) : null,
            'limit' => $request->get('limit') ? trim($request->get('limit')) : null,
            'page' => $request->get('page') ? trim($request->get('page')) : null,
            'filters' => $request->get('filters') ? trim($request->get('filters')) : null,
            'fields' => $request->get('fields') ? trim($request->get('fields')) : null,
            'format' => $request->get('format') ? trim($request->get('format')) : null,
        ];

        $includes = $this->parseIncludes($options['includes']);

        $sorts = $this->parseSorts($options['sorts']);

        $limit = $this->parseLimit($options['limit']);

        $page = $this->parsePage($options['page']);

        $fields = $this->parseFields($options['fields']);

        $format = $this->parseFormat($options['format']);

        $filters = $this->parseFilters($options['filters']);

        return new RequestParameters($includes, $sorts, $limit, $page, $filters, $fields, $format);
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
        $maxLimit = config('core.manager.max_limit');

        if (is_null($limit)) {
            return (int) $maxLimit;
        }

        return (int) ($maxLimit >= $limit) ? $limit : $maxLimit;
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

            $field = trim($rawArray[0]);
            $operator = trim($rawArray[1]);
            $value = trim(str_replace(';', ':', $rawArray[2]));
            $or = isset($rawArray[3]) ? 'or' : 'and';

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
                'value' => $value,
                'or' => $or
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
        return mb_strtolower($format);
    }
}
