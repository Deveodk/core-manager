<?php

namespace DeveoDK\Core\Manager\Paginators;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class Paginator
{
    /** @var Request */
    protected $request;

    /** @var array */
    protected $meta;

    /** @var array */
    protected $links;

    /**
     * Paginator constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param LengthAwarePaginator $lengthAwarePaginator
     */
    public function formatPaginator(LengthAwarePaginator $lengthAwarePaginator)
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

        $this->setMeta($meta);
        $this->setLinks($links);
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
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }
}
