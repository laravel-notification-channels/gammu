<?php

namespace NotificationChannels\Gammu\Drivers;

use Illuminate\Contracts\Config\Repository;

use NotificationChannels\Gammu\Models\Outbox;
use NotificationChannels\Gammu\Models\OutboxMultipart;
use NotificationChannels\Gammu\Models\Phone;

use NotificationChannels\Gammu\Exceptions\CouldNotSendNotification;
use Exception;

class DbDriver extends DriverAbstract
{
    protected $config;
    
    protected $outbox;
    
    protected $multipart;
    
    protected $phone;
    
    protected $data = [];
    
    protected $chunks = [];
    
    public function __construct(
        Repository $config, Outbox $outbox, OutboxMultipart $multipart, Phone $phone
    ) {
        $this->config = $config;
        $this->outbox = $outbox;
        $this->multipart = $multipart;
        $this->phone = $phone;
        
        $this->data['CreatorID'] = $this->getSignature();
    }
    
    public function send($phoneNumber, $content, $sender = null)
    {
        $this->setDestination($phoneNumber);
        $this->setContent($content);
        $this->setSender($sender);
        
        // Check Destination
        $this->getDestination();
        
        $outbox = $this->outbox->create($this->data);
        
        if (! empty($this->chunks) && ! empty($outbox->ID)) {
            foreach ($this->chunks as $chunk) {
                $chunk['ID'] = $outbox->ID;
                $this->multipart->create($chunk);
            }
        }
    }
    
    public function setDestination($phoneNumber)
    {
        if (empty($phoneNumber)) {
            throw CouldNotSendNotification::destinationNotProvided();
        }
        
        $this->data['DestinationNumber'] = trim($phoneNumber);
        
        return $this;
    }
    
    public function getDestination()
    {
        if (empty($this->data['DestinationNumber'])) {
            throw CouldNotSendNotification::destinationNotProvided();
        }
        
        return $this->data['DestinationNumber'];
    }
    
    public function setContent($content)
    {
        if (empty($content)) {
            throw CouldNotSendNotification::contentNotProvided();
        }
        
        if (strlen($content) > 160) {
            $this->parseLongMessage($content);
        } else {
            $this->data['TextDecoded'] = $content;
        }
        
        return $this;
    }
    
    public function getContent()
    {
        if (empty($this->data['TextDecoded'])) {
            throw CouldNotSendNotification::contentNotProvided();
        }
        
        $content = array_merge([$this->data['TextDecoded']], $this->chunks);
        
        return collect($content)->implode('');
    }
    
    public function setSender($sender = null)
    {
        if (empty($sender)) {
            $sender = $this->getDefaultSender(); 
        }
        
        $senders = $this->getSendersArray();
        
        if (! in_array($sender, $senders)) {
            throw CouldNotSendNotification::senderNotProvided();
        }
        
        $this->data['SenderID'] = $sender;
        
        return $this;
    }
    
    public function getSender()
    {
        if (empty($this->data['SenderID'])) {
            throw CouldNotSendNotification::senderNotProvided();
        }
        
        return $this->data['SenderID'];
    }
    
    private function getDefaultSender()
    {
        $sender = $this->config->get('services.gammu.sender');
        
        $senders = $this->getSendersArray();
        
        if (in_array($sender, $senders)) {
            return $sender;
        }
        
        try {
            return $this->phone->where('Send', 'yes')->firstOrFail()->ID;
        } catch (Exception $e) {
            throw CouldNotSendNotification::senderNotProvided();
        }
    }
    
    private function getSendersArray()
    {
        $senders = $this->phone->where('Send', 'yes')->get()->pluck('ID')->toArray();
        
        if (empty($senders)) {
            throw CouldNotSendNotification::senderNotProvided();
        }
        
        return $senders;
    }
    
    /**
     * Generate UDH part for long SMS.
     *
     * @link https://en.wikipedia.org/wiki/Concatenated_SMS#Sending_a_concatenated_SMS_using_a_User_Data_Header
     *
     * @return string
     */
    private function generateUDH($total = 2, $sequence = 2, $ref = 0)
    {
        // Length of User Data Header, in this case 05
        $octet1 = '05';

        // Information Element Identifier, equal to 00 (Concatenated short messages, 8-bit reference number)
        $octet2 = '00';

        // Length of the header, excluding the first two fields; equal to 03
        $octet3 = '03';

        // CSMS reference number, must be same for all the SMS parts in the CSMS
        $octet4 = str_pad(dechex($ref), 2, '0', STR_PAD_LEFT);

        // Total number of parts
        $octet5 = str_pad(dechex($total), 2, '0', STR_PAD_LEFT);

        // Part sequence
        $octet6 = str_pad(dechex($sequence), 2, '0', STR_PAD_LEFT);

        $udh = collect([
            $octet1, $octet2, $octet3, $octet4, $octet5, $octet6,
        ])->implode('');

        return strtoupper($udh);
    }

    protected function parseLongMessage($content)
    {
        // Parse message to chunks
        // @ref: http://www.nowsms.com/long-sms-text-messages-and-the-160-character-limit
        $messages = str_split($content, 153);
        $messages = collect($messages);
        $messagesCount = $messages->count();

        // Get first message
        $firstChunk = $messages->shift();

        // Generate UDH
        $ref = mt_rand(0, 255);
        $i = 1;
        $firstUDH = $this->generateUDH($messagesCount, $i, $ref);
        ++$i;

        $this->data['TextDecoded'] = $firstChunk;
        $this->data['UDH'] = $firstUDH;
        $this->data['MultiPart'] = 'true';

        foreach ($messages as $chunk) {
            array_push($this->chunks, [
                'UDH' => $this->generateUDH($messagesCount, $i, $ref),
                'TextDecoded' => $chunk,
                'SequencePosition' => $i,
            ]);
            ++$i;
        }

        return $this;
    }
} 
