<?php

namespace App\Services\Upload;

trait UploadTraits
{
    /**
     * @var string $filePath
     */
    protected static $filePath;

    /**
     * @var string $file
     */
    protected static $file;

    /**
     * Set the path where file will be stored.
     * @param string $filePath
     * @return self
     */
    public static function path(string $filePath): self
    {
        self::$filePath = $filePath;

        return new static;
    }

    /**
     * Set the  file
     * @param file $file
     */
    public static function file($file): self
    {
        self::$file = $file;
        return new static;
    }
}
