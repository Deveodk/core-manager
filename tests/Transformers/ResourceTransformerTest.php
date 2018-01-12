<?php

namespace DeveoDK\Tests\Resources;

use DeveoDK\Tests\Transformers\DummyResourceTransformer;
use Orchestra\Testbench\TestCase;

class ResourceTransformerTest extends TestCase
{
    /**
     * Setup function
     */
    public function setUp()
    {
        parent::setUp();
        require_once('DummyResourceTransformer.php');
    }

    /**
     * Can transform array
     * @test
     */
    public function canTransformArray()
    {
        $resourceTransformer = new DummyResourceTransformer();

        $dummyArray = [
            [
                'id' => 'test'
            ],
            [
                'id' => 'super'
            ]
        ];

        $transformed = $resourceTransformer->transform($dummyArray);

        $this->assertEquals($transformed['data'], $dummyArray);
    }

    /**
     * Can transform array to response
     * @test
     */
    public function canTransformArrayToResponse()
    {
        $resourceTransformer = new DummyResourceTransformer();

        $dummyArray = [
            [
                'id' => 'test'
            ],
            [
                'id' => 'super'
            ]
        ];

        $transformed = $resourceTransformer->transformToResponse($dummyArray);

        $decoded = json_decode($transformed->getContent());

        $this->assertEquals(2, count($decoded->data));
    }
}
