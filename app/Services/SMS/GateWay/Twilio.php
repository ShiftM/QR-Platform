<?php

namespace App\Services\SMS\GateWay;

use App\Services\SMS\SMStrait;
use Twilio\Rest\Client;

class Twilio
{

    use SMStrait;

    /**
     * @var string $client
     */
    protected static $client;

    /**
     * Create new instance of SMS
     */
    public function __construct()
    {
        self::$client = new Client(config('twilio.accountSid'), config('twilio.authToken'));
    }

    /**
     * send sms
     * @return void
     */
    public  static function send()
    {
        self::$client->messages->create(self::$to, [
            'from' => self::$from,
            'body' => self::$body
        ]);
    }
}
