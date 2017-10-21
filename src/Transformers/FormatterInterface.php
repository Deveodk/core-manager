<?php

namespace DeveoDK\Core\Manager\Transformers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

interface FormatterInterface
{
    /**
     * Format array XML
     * @param array $data
     * @param $status
     * @return Response
     */
    public function toXML(array $data, $status);

    /**
     * Format array to JSON
     * @param array $data
     * @param $status
     * @return JsonResponse
     */
    public function toJson(array $data, $status);

    /**
     * Format array to Yaml
     * @param array $data
     * @param $status
     * @return Response
     */
    public function toYaml(array $data, $status);
}
