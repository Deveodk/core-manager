<?php

namespace DeveoDK\Core\Manager\Parsers;

class RequestParameters
{
    /** @var array|null */
    protected $includes;

    /** @var array|null */
    protected $sorts;

    /** @var int|null */
    protected $limit;

    /** @var int|null */
    protected $page;

    /** @var array|null */
    protected $filters;

    /** @var array|null */
    protected $fields;

    /** @var string|null */
    protected $format;

    /**
     * RequestParameters constructor.
     * @param array|null $includes
     * @param array|null $sorts
     * @param int|null $limit
     * @param int|null $page
     * @param array|null $filters
     * @param array|null $fields
     * @param null|string $format
     */
    public function __construct(
        ?array $includes = null,
        ?array $sorts = null,
        ?int $limit = null,
        ?int $page = null,
        ?array $filters = null,
        ?array $fields = null,
        ?string $format = null
    ) {
        $this->includes = $includes;
        $this->sorts = $sorts;
        $this->limit = $limit;
        $this->page = $page;
        $this->filters = $filters;
        $this->fields = $fields;
        $this->format = $format;
    }

    /**
     * @return array|null
     */
    public function getIncludes(): ?array
    {
        return $this->includes;
    }

    /**
     * @param array|null $includes
     */
    public function setIncludes(?array $includes): void
    {
        $this->includes = $includes;
    }

    /**
     * @return array|null
     */
    public function getSorts(): ?array
    {
        return $this->sorts;
    }

    /**
     * @param array|null $sorts
     */
    public function setSorts(?array $sorts): void
    {
        $this->sorts = $sorts;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     */
    public function setPage(?int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return array|null
     */
    public function getFilters(): ?array
    {
        return $this->filters;
    }

    /**
     * @param array|null $filters
     */
    public function setFilters(?array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @return array|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param array|null $fields
     */
    public function setFields(?array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @return null|string
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @param null|string $format
     */
    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }
}
