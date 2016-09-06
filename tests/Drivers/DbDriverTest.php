<?php

namespace NotificationChannels\Gammu\Test\Drivers;

use NotificationChannels\Gammu\Test\TestBase;

use NotificationChannels\Gammu\Drivers\DbDriver;
use NotificationChannels\Gammu\Exceptions\CouldNotSendNotification;

use NotificationChannels\Gammu\Models\Phone;
use NotificationChannels\Gammu\Models\Outbox;

class DbDriverTest extends TestBase
{
    protected $driver;
    
    protected $phone;
    
    public function setUp()
    {
        parent::setUp();
        $this->driver = $this->app->make(DbDriver::class);
        $this->phone = $this->app->make(Phone::class);
    }
    
    protected function getParseLongMessageChunks($content)
    {
        $reflection = new \ReflectionClass(get_class($this->driver));
        $parseLongMessage = $reflection->getMethod('parseLongMessage');
        $parseLongMessage->setAccessible(true);
        $parseLongMessage->invokeArgs($this->driver, [
            'content' => $content
        ]);
        $chunks = $reflection->getProperty('chunks');
        $chunks->setAccessible(true);
        
        return $chunks->getValue($this->driver);
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
    
    public function test_empty_sender_on_single_phone()
    {
        $this->addPhone('telkomsel');
        
        $sender = $this->driver->getSender();
        
        $this->assertEquals('telkomsel', $sender);
    }
    
    public function test_empty_sender_on_multiple_phones()
    {
        $this->addPhone('indosat');
        $this->addPhone('telkomsel');
        
        $sender = $this->driver->getSender();
        
        $this->assertEquals('indosat', $sender);
    }
    
    public function test_set_unknown_sender_on_single_phone()
    {
        $this->addPhone('xl');
        
        $this->app->config->set('services.gammu.sender', 'indosat');
        
        $sender = $this->driver->getSender();
        
        $this->assertEquals('xl', $sender);
    }
    
    public function test_set_unknown_sender_on_multiple_phones()
    {
        $this->addPhone('tri');
        $this->addPhone('xl');
        
        $this->app->config->set('services.gammu.sender', 'telkomsel');
        
        $sender = $this->driver->getSender();
        
        $this->assertEquals('tri', $sender);
    }
    
    public function test_set_sender_on_single_phone()
    {
        $this->addPhone('tri');
        
        $this->app->config->set('services.gammu.sender', 'tri');
        
        $sender = $this->driver->getSender();
        
        $this->assertEquals('tri', $sender);
    }
    
    public function test_set_sender_on_multiple_phones()
    {
        $this->addPhone('telkomsel');
        $this->addPhone('indosat');
        $this->addPhone('xl');
        
        $this->app->config->set('services.gammu.sender', 'xl');
        
        $sender = $this->driver->getSender();
        
        $this->assertEquals('xl', $sender);
    }
    
    public function test_no_available_phones()
    {
        $this->addPhone('telkomsel', 'no');
        $this->addPhone('indosat', 'no');
        
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->getSender();
    }
    
    public function test_set_empty_phone()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->setSender(null);
    }
    
    public function test_empty_phone()
    {
        $this->setExpectedException(CouldNotSendNotification::class);
        
        $this->driver->getSender();
    }
    
    public function test_parse__sms()
    {
        $content = 'Republik Indonesia, disingkat RI atau Indonesia';
        
        $chunks = $this->getParseLongMessageChunks($content);
        $chunksCount = count($chunks);
        
        $this->assertFalse($this->driver->isLongSms);
        $this->assertFalse($this->driver->isLongSms);
    }
    
    public function test_parse_long_sms()
    {
        $content = 'Republik Indonesia, disingkat RI atau Indonesia, adalah ';
        $content.= 'negara di Asia Tenggara yang dilintasi garis khatulistiwa ';
        $content.= 'dan berada di antara benua Asia dan Australia serta antara ';
        $content.= 'Samudra Pasifik dan Samudra Hindia.';
        
        $chunks = $this->getParseLongMessageChunks($content);
        $chunksCount = count($chunks);
        
        $this->assertGreaterThanOrEqual(1, $chunksCount);
        $this->assertTrue($this->driver->isLongSms);
    }
    
    public function test_160_char_is_not_long_sms()
    {
        $content = 'Republik Indonesia, disingkat RI atau Indonesia, adalah ';
        $content.= 'negara di Asia Tenggara yang dilintasi garis khatulistiwa ';
        $content.= 'dan berada di antara benua Asia dan Australia.';
        
        $this->driver->setContent($content);
        
        $this->assertFalse($this->driver->isLongSms);
    }
    
    public function test_161_char_is_long_sms()
    {
        $content = 'Republik Indonesia, disingkat RI atau Indonesia, adalah ';
        $content.= 'negara di Asia Tenggara yang dilintasi garis khatulistiwa ';
        $content.= 'dan berada di antara benua Asia dan Australia..';
        
        $this->driver->setContent($content);
        
        $this->assertTrue($this->driver->isLongSms);
    }
    
    public function test_send()
    {
        $phoneNumber = $this->faker->e164PhoneNumber;
        $content = $this->faker->sentence;
        $sender = 'telkomsel';
        
        $this->addPhone($sender);
        
        $this->driver->send($phoneNumber, $content, $sender);
        
        $this->assertEquals($phoneNumber, $this->driver->getDestination());
        $this->assertEquals($content, $this->driver->getContent());
        $this->assertEquals($sender, $this->driver->getSender());
        
        $outbox = $this->app->make(Outbox::class);
        $sms = $outbox->get()->last();
        
        $this->assertEquals($phoneNumber, $sms->DestinationNumber);
        $this->assertEquals($content, $sms->TextDecoded);
        $this->assertEquals($sender, $sms->SenderID);
        $this->assertEquals($this->driver->getSignature(), $sms->CreatorID);
    }
    
    private function addPhone($sender, $send = 'yes')
    {
        $this->phone->create([
            'ID' => $sender,
            'IMEI' => $this->faker->numerify('#############'),
            'Client' => $this->driver->getSignature(),
            'Send' => $send,
        ]);
    }
}
