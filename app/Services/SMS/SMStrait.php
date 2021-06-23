<?php

namespace App\Services\SMS;

trait SMStrait
{

    /**
     * @property string $from;
     */
    protected static $from;

    /**
     * @property string $body;
     */
    protected static $body;

        /**
     * @property string $to;
     */
    protected static $to;

    public static function from(string $from): self
    {
        self::$from = $from;
        return new static;
    }

    public static function body(string $body): self
    {
        self::$body = $body;
        return new static;
    }

    public static function to(string $to): self
    {
        self::$to = $to;
        return new static;
    }
}
