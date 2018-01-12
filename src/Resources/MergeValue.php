<?php

namespace DeveoDK\Core\Manager\Resources;

use Illuminate\Support\Collection;

class MergeValue
{
    /** @var array|Collection */
    protected $data;

    /**
     * Create new merge value instance.
     *
     * @param  Collection|array  $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data instanceof Collection ? $data->all() : $data;
    }

    /**
     * @return array|Collection
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|Collection $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}
