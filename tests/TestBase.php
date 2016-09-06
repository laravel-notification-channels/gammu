<?php
namespace NotificationChannels\Gammu\Test;

use NotificationChannels\Gammu\GammuServiceProvider;

use Orchestra\Testbench\TestCase;
use Mockery;
use Faker\Factory;

abstract class TestBase extends TestCase
{
    const MIGRATIONS_PATH = 'migrations';
    
    protected $faker;
    
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        $this->resetDatabase();
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
        $app['path.base'] = __DIR__.'/..';
        $app['config']->set('database.connections.gammu', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'    => '',
        ]);
    }

    private function resetDatabase()
    {
        $this->loadMigrationsFrom([
            '--database' => 'gammu',
            '--realpath' => $this->getMigrationsPath(),
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [GammuServiceProvider::class];
    }

    public function mock($className)
    {
        return Mockery::mock($className);
    }
    
    public function getMigrationsPath()
    {
        return realpath(__DIR__.DIRECTORY_SEPARATOR.self::MIGRATIONS_PATH);
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }
}