<?php

namespace DeveoDK\Tests\Paginators;

use Carbon\Carbon;
use DeveoDK\Core\Manager\Paginators\Paginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

class PaginatorTest extends TestCase
{
    /** @var DatabaseManager */
    protected $databaseManager;

    /**
     * Setup function
     */
    public function setUp()
    {
        parent::setUp();
        $this->databaseManager = app(DatabaseManager::class);
        $this->loadLaravelMigrations('testbench');
        $this->artisan('migrate', ['--database' => 'testbench']);
    }

    /**
     * PaginatorTest constructor.
     * @param null|string $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Can format paginator
     * @test
     */
    public function canFormatPaginator()
    {
        $request = new Request();

        $paginator = new Paginator($request);

        $this->databaseManager->table('users')->insert([
            'name' => 'Orchestra',
            'email' => 'hello@orchestraplatform.com',
            'password' => 'asdfsdfsdfsdf',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $users = $this->databaseManager->table('users')->paginate();

        $paginator->formatPaginator($users);

        $links = $paginator->getLinks();
        $meta = $paginator->getMeta();

        $combined = array_merge($links, $meta);

        $this->assertArrayHasKey('total', $combined);
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
