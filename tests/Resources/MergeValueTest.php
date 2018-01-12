<?php

namespace DeveoDK\Tests\Resources;

use Carbon\Carbon;
use DeveoDK\Core\Manager\Paginators\Paginator;
use DeveoDK\Core\Manager\Resources\MergeValue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

class MergeValueTest extends TestCase
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
    public function canPopulateMergeData()
    {
        $mergeValue = new MergeValue(['test' => 'super']);

        $this->assertArrayHasKey('test', $mergeValue->getData());
    }
}
