<?php

namespace DeveoDK\Tests\Parsers;

use DeveoDK\Core\Manager\Parsers\RequestParameterParser;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

class RequestParameterParserTest extends TestCase
{
    /**
     * Setup function
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test that the resource option can parse includes
     * @test
     */
    public function canParseIncludes()
    {
        $includesArray = [
            'test',
            'test'
        ];

        $request = (new Request())->merge(['includes' => 'test,test']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($includesArray, $output->getIncludes());
    }

    /**
     * Can parse includes with space between
     * @test
     */
    public function canParseIncludesWithSpace()
    {
        $includesArray = [
            'test',
            'test'
        ];

        $request = (new Request())->merge(['includes' => 'test,  test']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($includesArray, $output->getIncludes());
    }

    /**
     * Can parse and exclude extra comma in includes
     * @test
     */
    public function canParseAndExcludeIncludesWithExtraComma()
    {
        $includesArray = [
            'test',
            'test'
        ];

        $request = (new Request())->merge(['includes' => 'test,test,']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($includesArray, $output->getIncludes());
    }

    /**
     * Can parse includes snake_case into camelCase
     * @test
     */
    public function canParseIncludesSnakeCaseIntoCamelCase()
    {
        $includesArray = [
            'awesomeTest',
            'test'
        ];

        $request = (new Request())->merge(['includes' => 'awesome_test,test,']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($includesArray, $output->getIncludes());
    }

    /**
     * Can parse includes and use alias
     * The alias is dummy => super
     * @test
     */
    public function canParseIncludesAndUseAlias()
    {
        $includesArray = [
            'test',
            'super'
        ];

        $request = (new Request())->merge(['includes' => 'test,dummy']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($includesArray, $output->getIncludes());
    }

    /**
     * Can parse limit
     * @test
     */
    public function canParseLimit()
    {
        $request = (new Request())->merge(['limit' => 10]);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals(10, $output->getLimit());
    }

    /**
     * Can parse limit as string
     * @test
     */
    public function canParseStringLimit()
    {
        $request = (new Request())->merge(['limit' => '10']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals(10, $output->getLimit());
    }

    /**
     * Can parse limit as double
     * @test
     */
    public function canParseDoubleLimit()
    {
        $request = (new Request())->merge(['limit' => 10.0]);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals(10, $output->getLimit());
    }

    /**
     * Can parse page
     * @test
     */
    public function canParsePage()
    {
        $request = (new Request())->merge(['page' => 10]);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals(10, $output->getPage());
    }

    /**
     * Can parse page as string
     * @test
     */
    public function canParsePageAsString()
    {
        $request = (new Request())->merge(['page' => '10']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals(10, $output->getPage());
    }

    /**
     * Can parse page as string
     * @test
     */
    public function canParsePageAsDouble()
    {
        $request = (new Request())->merge(['page' => 10.0]);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals(10, $output->getPage());
    }

    /**
     * can parse fields
     * @test
     */
    public function canParseFields()
    {
        $fieldsArray = [
            'id',
            'test',
            'super.id'
        ];

        $request = (new Request())->merge(['fields' => 'id,test,super.id']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($fieldsArray, $output->getFields());
    }

    /**
     * can parse fields and exclude extra comma
     * @test
     */
    public function canParseFieldsAndExcludeExtraComma()
    {
        $fieldsArray = [
            'id',
            'test',
            'super.id'
        ];

        $request = (new Request())->merge(['fields' => 'id,test,super.id,']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($fieldsArray, $output->getFields());
    }

    /**
     * can parse fields and use alias
     * The alias is dummy => super
     * @test
     */
    public function canParseFieldsAndUseAlias()
    {
        $fieldsArray = [
            'id',
            'test',
            'super.id',
            'super'
        ];

        $request = (new Request())->merge(['fields' => 'id,test,super.id,dummy']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($fieldsArray, $output->getFields());
    }

    /**
     * can parse filters
     * @test
     */
    public function canParseFilters()
    {
        $fieldsArray = [
            [
                'field' => 'active',
                'operator' => '=',
                'value' => 'super',
                'or' => 'and'
            ],
            [
                'field' => 'super',
                'operator' => '=',
                'value' => true,
                'or' => 'and'
            ]
        ];

        $request = (new Request())->merge(['filters' => 'active:=:super,super:=:true']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($fieldsArray, $output->getFilters());
    }

    /**
     * can parse filters
     * @test
     */
    public function canParseFiltersOr()
    {
        $fieldsArray = [
            [
                'field' => 'active',
                'operator' => '=',
                'value' => 'super',
                'or' => 'and'
            ],
            [
                'field' => 'super',
                'operator' => '=',
                'value' => true,
                'or' => 'and'
            ]
        ];

        $request = (new Request())->merge(['filters' => 'active:=:super,super:=:true']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($fieldsArray, $output->getFilters());
    }

    /**
     * Can parse filters with array
     * @test
     */
    public function canParseFiltersWithArrays()
    {
        $filtersArray = [
            [
                'field' => 'active',
                'operator' => '=',
                'value' => [
                    'super',
                    'mega',
                    'awesome'
                ],
                'or' => 'and'
            ]
        ];

        $request = (new Request())->merge(['filters' => 'active:=:super|mega|awesome']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($filtersArray, $output->getFilters());
    }

    /**
     * Can parse filters with bool
     * @test
     */
    public function canParseFiltersWithBool()
    {
        $request = (new Request())->merge(['filters' => 'active:=:true,active:=:false']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals(true, $output->getFilters()[0]['value']);
    }

    /**
     * Can parse sorts
     * @test
     */
    public function canParseSorts()
    {
        $sortsArray = [
            [
                'column' => 'id',
                'direction' => 'DESC',
                'table' => 'super'
            ]
        ];

        $request = (new Request())->merge(['sorts' => 'id:desc:super']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($sortsArray, $output->getSorts());
    }

    /**
     * Can parse sorts with only column set
     * @test
     */
    public function canParseSortWithOnlyColumnSet()
    {
        $sortsArray = [
            [
                'column' => 'id',
                'direction' => 'ASC',
                'table' => null
            ]
        ];

        $request = (new Request())->merge(['sorts' => 'id']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals($sortsArray, $output->getSorts());
    }

    /**
     * Can parse format as json
     * @test
     */
    public function canParseFormatJson()
    {
        $request = (new Request())->merge(['format' => 'json']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals('json', $output->getFormat());
    }

    /**
     * Can parse format as xml
     * @test
     */
    public function canParseFormatXml()
    {
        $request = (new Request())->merge(['format' => 'xml']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals('xml', $output->getFormat());
    }

    /**
     * Can parse format as yaml
     * @test
     */
    public function canParseFormatYaml()
    {
        $request = (new Request())->merge(['format' => 'yaml']);
        $requestParameterParser = $this->getParameterParser($request);

        $output = $requestParameterParser->parseResourceOptions();

        $this->assertEquals('yaml', $output->getFormat());
    }

    /**
     * @param Request $request
     * @return RequestParameterParser
     */
    protected function getParameterParser(Request $request)
    {
        return new RequestParameterParser($request, ['dummy' => 'super'], ['dummy' => 'super']);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('core.manager.max_limit', 100);
    }
}
