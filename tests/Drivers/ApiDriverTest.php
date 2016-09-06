<?php

namespace NotificationChannels\Gammu\Test\Drivers;

use NotificationChannels\Gammu\Test\TestBase;

use NotificationChannels\Gammu\Drivers\ApiDriver;
use NotificationChannels\Gammu\Exceptions\CouldNotSendNotification;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class ApiDriverTest extends TestBase implements DriverTestInterface
{
    protected $driver;
    
    protected $guzzle;
    
    public function setUp()
    {
        parent::setUp();
        $this->driver = $this->app->make(ApiDriver::class);
        $this->guzzle = $this->mock(Client::class);
    }
    
    public function test_set_destination()
    {
        $phoneNumber = $this->faker->e164PhoneNumber;
        $this->driver->setDestination($phoneNumber);
        
        $this->assertEquals($phoneNumber, $this->driver->getDestination());
    }
    
    public function test_empty_destination()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->getDestination();
    }
    
    public function test_set_empty_destination()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->setDestination(null);
    }
    
    public function test_set_content()
    {
        $content = $this->faker->sentence;
        $this->driver->setContent($content);
        
        $this->assertEquals($content, $this->driver->getContent());
    }
    
    public function test_empty_content()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->getContent();
    }
    
    public function test_set_empty_content()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->setContent(null);
    }
    
    public function test_set_url()
    {
        $url = $this->faker->url;
        $this->driver->setUrl($url);
        
        $this->assertEquals($url, $this->driver->getUrl());
    }
    
    public function test_set_url_config()
    {
        $url = $this->faker->url;
        
        $this->app->config->set('services.gammu.url', $url);
        
        $this->assertEquals($url, $this->driver->getUrl());
    }
    
    public function test_empty_url()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->getUrl();
    }
    
    public function test_set_empty_url()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->getUrl(null);
    }
    
    public function test_set_key()
    {
        $key = $this->faker->md5;
        $this->app->config->set('services.gammu.auth', $key);
        
        $this->driver->setKey();
        
        $this->assertEquals($key, $this->driver->getKey());
    }
    
    public function test_empty_key()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->getKey();
    }
    
    public function test_set_empty_key()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->setKey();
    }
    
    private function mockDriver()
    {
        return new ApiDriver($this->app->config, $this->guzzle);
    }
    
    private function setConfig()
    {
        $key = $this->faker->md5;
        $url = 'http://guzzle.api/';
        $this->app->config->set('services.gammu.auth', $key);
        $this->app->config->set('services.gammu.url', $url);
        
        return $this;
    }
    
    public function test_send()
    {
        $phoneNumber = $this->faker->e164PhoneNumber;
        $content = $this->faker->sentence;
        
        $this->setConfig();
        
        $body = 'Sent message to: '.$phoneNumber;
        
        $this->guzzle->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], $body));
        
        $driver = $this->mockDriver();
        $driver->send($phoneNumber, $content);
        
        $this->assertEquals(200, $driver->apiStatusCode);
        $this->assertEquals($body, $driver->apiResponseBody);        
    }
}
