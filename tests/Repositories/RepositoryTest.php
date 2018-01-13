<?php

namespace DeveoDK\Tests\Repositories;

use Carbon\Carbon;
use DeveoDK\Core\Manager\Parsers\RequestParameters;
use DeveoDK\Tests\Databases\DummyEntity;
use Faker\Factory;
use Illuminate\Database\DatabaseManager;
use Orchestra\Testbench\TestCase;

class RepositoryTest extends TestCase
{
    /** @var DatabaseManager */
    protected $databaseManager;

    /**
     * Setup function
     */
    public function setUp()
    {
        parent::setUp();

        require_once('DummyRepository.php');
        require_once(base_path() . '/../../../../tests/Databases/DummyEntity.php');

        $this->loadLaravelMigrations('testbench');
        $this->artisan('migrate', ['--database' => 'testbench']);
        $this->databaseManager = app(DatabaseManager::class);

        $this->populateDB();
    }

    /**
     * Can find id
     * @test
     */
    public function canFindId()
    {
        $repository = new DummyRepository();

        $result = $repository->findById(new RequestParameters(), 1);

        $this->assertEquals(true, (isset($result)));
    }

    /**
     * Can find all
     * @test
     */
    public function canFindAll()
    {
        $repository = new DummyRepository();

        $result = $repository->findAll(new RequestParameters());

        $this->assertEquals(100, count($result));
    }

    /**
     * Can find by field
     * @test
     */
    public function canFindByField()
    {
        $repository = new DummyRepository();

        $result = $repository->findBy(new RequestParameters(), 'id', '=', 1);

        $this->assertEquals(1, $result->getAttribute('id'));
    }

    /**
     * Can find all where
     * @test
     */
    public function canFindAllWhere()
    {
        $repository = new DummyRepository();

        $result = $repository->findAllWhere(new RequestParameters(), 'id', 1);

        $this->assertEquals(1, count($result));
    }

    /**
     * Can count
     * @test
     */
    public function canCount()
    {
        $repository = new DummyRepository();

        $result = $repository->count(new RequestParameters());

        $this->assertEquals(100, $result);
    }

    /**
     * Can sum
     * @test
     */
    public function canSum()
    {
        $repository = new DummyRepository();

        $result = $repository->sum(new RequestParameters(), 'id');

        $this->assertEquals(5050, $result);
    }

    /**
     * Can delete
     * @test
     * @throws \Exception
     */
    public function canDelete()
    {
        $repository = new DummyRepository();

        $data = $repository->findBy(new RequestParameters(), 'id', '=', 1);

        $repository->delete($data);

        $data = $repository->findBy(new RequestParameters(), 'id', '=', 1);

        $this->assertEquals(null, $data);
    }

    /**
     * Can create
     * @test
     */
    public function canCreate()
    {
        $repository = new DummyRepository();

        $dummyEntity = new DummyEntity();
        $dummyEntity->setAttribute('name', 'test');
        $dummyEntity->setAttribute('email', 'asdf@asts.dk');
        $dummyEntity->setAttribute('password', 'asdfasfasdf');

        $result = $repository->create($dummyEntity);

        $this->assertEquals(true, isset($result));
    }

    /**
     * Can update
     * @test
     */
    public function canUpdate()
    {
        $repository = new DummyRepository();

        $existingEntity = $repository->findById(new RequestParameters(), 1);
        $existingName = $existingEntity->getAttribute('name');

        $existingEntity->setAttribute('name', 'Deveo');

        $updatedEntity = $repository->update($existingEntity);
        $updatedName = $updatedEntity->getAttribute('name');

        $this->assertNotEquals($existingName, $updatedName);
    }

    /**
     * Can apply authorization
     * @test
     */
    public function canApplyAuthorization()
    {
        $repository = new DummyRepository();

        $repository->applyAuthorization('id', 1);

        $results = $repository->findAll(new RequestParameters());

        $this->assertEquals(1, count($results));
    }

    /**
     * Can find all where in
     * @test
     */
    public function canFindAllWhereIn()
    {
        $repository = new DummyRepository();

        $whereIn = [];

        for ($i = 1; $i <= 100; $i++) {
            array_push($whereIn, $i);
        }

        $result = $repository->findAllWhereIn(new RequestParameters(), 'id', $whereIn);

        $this->assertEquals(100, count($result));
    }

    /**
     * Populate DB before test
     */
    protected function populateDB()
    {
        $this->databaseManager->table('users')->delete();

        $users = [];

        for ($i = 0; $i < 100; $i++) {
            $faker = Factory::create();

            array_push($users, [
                'name' => $faker->name,
                'email' => $faker->unique()->email,
                'password' => $faker->password,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->databaseManager->table('users')->insert($users);
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
    }
}
