<?php

namespace DeveoDK\Core\Manager\Formatters;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Yaml\Yaml;

class Formatter implements FormatterInterface
{
    /** @var string */
    protected $wrap;

    /**
     * @param array $data
     * @param int $status
     * @param string $format
     * @return JsonResponse|Response
     */
    public function toResponse(array $data, int $status, ?string $format)
    {
        switch ($format) {
            case 'xml':
                return $this->toXML($data, $status);
            case 'yaml':
                return $this->toYaml($data, $status);
            case 'yml':
                return $this->toYaml($data, $status);
            case 'json':
                return $this->toJson($data, $status);
            default:
                return $this->toJson($data, $status);
        }
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
            $data,
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
        $yaml = Yaml::dump($data);
        return response()->make($yaml, $status, ['content-type' => 'text/yaml']);
    }

    /**
     * @param $data
     * @param $status
     * @return JsonResponse
     */
    public function toJson(array $data, $status)
    {
        return response()->json($data, $status);
    }
}
