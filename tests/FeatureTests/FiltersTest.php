<?php

namespace DeveoDK\Tests\FeatureTests;

use Carbon\Carbon;
use DeveoDK\Tests\Repositories\DummyRepository;
use DeveoDK\Tests\Transformers\DummyResourceTransformer;
use Faker\Factory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

class FiltersTest extends TestCase
{
    /** @var DatabaseManager */
    protected $databaseManager;

    /**
     * Setup function
     */
    public function setUp()
    {
        parent::setUp();

        require_once(base_path() . '/../../../../tests/Repositories/DummyRepository.php');
        require_once(base_path() . '/../../../../tests/Databases/DummyEntity.php');
        require_once(base_path() . '/../../../../tests/Transformers/DummyResourceTransformer.php');

        $this->loadLaravelMigrations('testbench');
        $this->artisan('migrate', ['--database' => 'testbench']);
        $this->databaseManager = app(DatabaseManager::class);

        $this->populateDB();
    }

    /**
     * Can filter where equal
     * @test
     */
    public function canFilterWhereEqual()
    {
        $request = new Request();
        $request->query->set('filters', 'id:=:1');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(1, count($transformed['data']));
    }

    /**
     * Can filter where not equal
     * @test
     */
    public function canFilterWhereNotEqual()
    {
        $request = new Request();
        $request->query->set('filters', 'id:!=:1');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(99, count($transformed['data']));
    }

    /**
     * Can filter where bigger than
     * @test
     */
    public function canFilterWhereBiggerThan()
    {
        $request = new Request();
        $request->query->set('filters', 'id:>:1');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(99, count($transformed['data']));
    }

    /**
     * Can filter where smaller than
     * @test
     */
    public function canFilterWhereSmallerThan()
    {
        $request = new Request();
        $request->query->set('filters', 'id:<:1');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(0, count($transformed['data']));
    }

    /**
     * Can filter where smaller than
     * @test
     */
    public function canFilterWhereSmallerThanOrEqual()
    {
        $request = new Request();
        $request->query->set('filters', 'id:<=:1');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(1, count($transformed['data']));
    }

    /**
     * Can filter where smaller than
     * @test
     */
    public function canFilterWhereBiggerThanOrEqual()
    {
        $request = new Request();
        $request->query->set('filters', 'id:>=:1');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(100, count($transformed['data']));
    }

    /**
     * Can filter where not equal to
     * @test
     */
    public function canFilterWhereNotEqualTo()
    {
        $request = new Request();
        $request->query->set('filters', 'id:<>:1');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(99, count($transformed['data']));
    }

    /**
     * Can filter where like
     * @test
     */
    public function canFilterWhereLike()
    {
        $request = new Request();
        $request->query->set('filters', 'id:like:%1');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(10, count($transformed['data']));
    }

    /**
     * Can filter where in
     * @test
     */
    public function canFilterWhereIn()
    {
        $request = new Request();
        $request->query->set('filters', 'id:in:1|2|3|4|5');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(5, count($transformed['data']));
    }

    /**
     * Can filter where not in
     * @test
     */
    public function canFilterWhereNotIn()
    {
        $request = new Request();
        $request->query->set('filters', 'id:not_in:1|2|3|4|5');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(95, count($transformed['data']));
    }

    /**
 * Can filter where between
 * @test
 */
    public function canFilterWhereBetween()
    {
        $request = new Request();
        $request->query->set('filters', 'id:between:1|50');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(50, count($transformed['data']));
    }

    /**
     * Can filter where not between
     * @test
     */
    public function canFilterWhereNotBetween()
    {
        $request = new Request();
        $request->query->set('filters', 'id:not_between:1|20');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(80, count($transformed['data']));
    }

    /**
     * Can filter where month
     * @test
     */
    public function canFilterWhereMonth()
    {
        $request = new Request();
        $request->query->set('filters', 'created_at:month:'.date('m'));

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(100, count($transformed['data']));
    }

    /**
     * Can filter where day
     * @test
     */
    public function canFilterWhereDay()
    {
        $request = new Request();
        $request->query->set('filters', 'created_at:day:'.date('d'));

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(100, count($transformed['data']));
    }

    /**
     * Can filter where year
     * @test
     */
    public function canFilterWhereYear()
    {
        $request = new Request();
        $request->query->set('filters', 'created_at:year:'.date('Y'));

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(100, count($transformed['data']));
    }

    /**
     * Can filter where year
     * test
     */
    public function canFilterWhereTime()
    {
        $request = new Request();
        $request->query->set('filters', 'created_at:time:'.date('H;i'));

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(100, count($transformed['data']));
    }

    /**
     * Can filter where date
     * @test
     */
    public function canFilterWhereDate()
    {
        $request = new Request();
        $request->query->set('filters', 'created_at:date:'.date('Y-m-d'));

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(100, count($transformed['data']));
    }

    /**
     * Can sort by desc
     * @test
     */
    public function canSortByDesc()
    {
        $request = new Request();
        $request->query->set('sorts', 'id:desc');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(100, $transformed['data'][0]['id']);
    }

    /**
     * Can sort by asc
     * @test
     */
    public function canSortByAsc()
    {
        $request = new Request();
        $request->query->set('sorts', 'id:asc');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(1, $transformed['data'][0]['id']);
    }

    /**
     * Can filter out by fields
     * @test
     */
    public function canFilterOutFields()
    {
        $request = new Request();
        $request->query->set('fields', 'unlikelyField');

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(true, empty($transformed['data'][0]));
    }

    /**
     * Can use limit
     * @test
     */
    public function canUseLimit()
    {
        $request = new Request();
        $request->query->set('limit', 1);

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(1, count($transformed['data']));
    }

    /**
     * Can use pagination
     * @test
     */
    public function canPaginate()
    {
        $request = new Request();
        $request->query->set('page', 2);

        $repository = new DummyRepository();
        $resourceTransformer = new DummyResourceTransformer($request);

        $requestParameters = $resourceTransformer->parseResourceOptions();

        $data = $repository->findAll($requestParameters);

        $transformed = $resourceTransformer->transform($data);

        $this->assertEquals(2, $transformed['meta']['current_page']);
    }

    /**
     * Populate DB before test
     */
    protected function populateDB()
    {
        $this->databaseManager->table('users')->delete();

        for ($i = 0; $i < 100; $i++) {
            $faker = Factory::create();

            $this->databaseManager->table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->email,
                'password' => $faker->password,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
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
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('core.manager.wrap', 'data');
        $app['config']->set('core.manager.includes_wrap', false);
    }
}
