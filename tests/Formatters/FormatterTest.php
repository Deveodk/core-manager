<?php

namespace DeveoDK\Tests\Formatters;

use DeveoDK\Core\Manager\Formatters\Formatter;
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
     * Can format JSON
     * @test
     */
    public function canFormatJson()
    {
        $formatter = new Formatter();

        $formatted = $formatter->toResponse(['data' => 'super'], 200, 'json');

        $contentType = $formatted->headers->get('content-type');

        $this->assertEquals('application/json', $contentType);
    }

    /**
     * Can format XML
     * @test
     */
    public function canFormatXML()
    {
        $formatter = new Formatter();

        $formatted = $formatter->toResponse(['data' => 'super'], 200, 'xml');

        $contentType = $formatted->headers->get('content-type');

        $this->assertEquals('application/xml', $contentType);
    }

    /**
     * Can format YAML
     * @test
     */
    public function canFormatYAML()
    {
        $formatter = new Formatter();

        $formatted = $formatter->toResponse(['data' => 'super'], 200, 'yml');

        $contentType = $formatted->headers->get('content-type');

        $this->assertEquals('text/yaml', $contentType);
    }
}
