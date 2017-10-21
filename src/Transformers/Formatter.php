<?php

namespace DeveoDK\Core\Manager\Transformers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Yaml\Yaml;

class Formatter implements FormatterInterface
{
    /** @var string */
    public $wrap;

    /** @var array */
    public $meta;

    /** @var array */
    public $with;

    public function __construct(array $meta = [], array $with = [], string $wrap = 'data')
    {
        $this->wrap = 'data';
        $this->meta = $meta;
        $this->with = $with;
    }

    /**
     * @param $data
     * @param $status
     * @return Response
     */
    public function toXML(array $data, $status)
    {
        $data = [
            'item' => [
                $data
            ]
        ];

        $xml = ArrayToXml::convert(
            $this->wrap($data),
            'root',
            true,
            'utf-8',
            '1.0'
        );

        return response()->make($xml, $status, ['content-type' => 'application/xml']);
    }

    /**
     * @param $data
     * @param $status
     * @return Response
     */
    public function toYaml(array $data, $status)
    {
        $yaml = Yaml::dump($this->wrap($data));
        return response()->make($yaml, $status, ['content-type' => 'text/yaml']);
    }

    /**
     * @param $data
     * @param $status
     * @return JsonResponse
     */
    public function toJson(array $data, $status)
    {
        $json = $this->wrap($data);
        return response()->json($json, $status);
    }

    /**
     * Wrap the given data if necessary.
     *
     * @param  array  $data
     * @return array
     */
    protected function wrap($data)
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        }

        $data = [$this->wrap => $data];

        return array_merge_recursive($data, $this->getMeta(), $this->getWith());
    }

    /**
     * @return string
     */
    public function getWrap(): string
    {
        return $this->wrap;
    }

    /**
     * @param string $wrap
     */
    public function setWrap(string $wrap)
    {
        $this->wrap = $wrap;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        if ($this->meta === ['meta' => []]) {
            return [];
        }

        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function setMeta(array $meta)
    {
        if (isset($this->meta['meta'])) {
            $this->meta['meta'] = array_merge($this->meta['meta'], $meta);
            return;
        }

        $this->meta['meta'] = array_merge($this->meta, $meta);
    }

    /**
     * @return array
     */
    public function getWith(): array
    {
        return $this->with;
    }

    /**
     * @param array $with
     */
    public function setWith(array $with)
    {
        $this->with = array_merge($this->with, $with);
    }
}
