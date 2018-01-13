<?php

namespace DeveoDK\Core\Manager\Formatters;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

interface FormatterInterface
{
    /**
     * @param array $data
     * @param int $status
     * @param string $format
     * @return JsonResponse|Response
     */
    public function toResponse(array $data, int $status, string $format);
}
