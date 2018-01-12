<?php

namespace DeveoDK\Tests\Resources;

use DeveoDK\Core\Manager\Resources\MergeValue;
use DeveoDK\Core\Manager\Resources\Relation;
use Orchestra\Testbench\TestCase;

class RelationTest extends TestCase
{
    /**
     * Setup function
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Can populate merge data
     * @test
     */
    public function canPopulateRelationTest()
    {
        $relationTest = new Relation(['test' => 'super']);

        $this->assertArrayHasKey('test', $relationTest->getData());
    }
}
