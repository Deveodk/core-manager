<?php

namespace DeveoDK\Tests\Transformers;

use DeveoDK\Core\Manager\Transformers\ResourceTransformer;

class DummyResourceTransformer extends ResourceTransformer
{
    /**
     * @param $data
     * @return array
     */
    protected function resourceData($data)
    {
        return [
            'id' => $data['id'],
        ];
    }

    public function extra()
    {
        return [
            'super' => [
                'data' => 'this is extra'
            ]
        ];
    }

    public function meta()
    {
        return [
            'data' => 'this is data!'
        ];
    }
}
