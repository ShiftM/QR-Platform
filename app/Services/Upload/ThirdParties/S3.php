<?php

namespace App\Services\Upload\ThirdParties;

use App\Services\Upload\UploadTraits;
use Illuminate\Support\Facades\Storage;

/**
 * S3 storage traits
 */
trait S3
{

    use UploadTraits;

    /**
     * upload file to s3 bucket
     * @return array
     */
    public static function s3() : array
    {
        $uploadToS3 = Storage::disk('s3');
        $aws = config('filesystems')['disks']['s3'];

        $basePath = $aws['url'];
        $name = self::$file->getClientOriginalName();
        $name = str_replace(' ', '_', $name);
        $extension = self::$file->getClientOriginalExtension();

        $fileName = $name . '_' . time() . '.' . $extension;

        $uploadToS3->put(
            self::$filePath . "/" . $fileName, file_get_contents(self::$file->getRealPath()),
            'public'
        );

        $res = [
            'path' => $basePath . self::$filePath . "/",
            'fileName' => $fileName,
            'fullPath' => $basePath . self::$filePath . "/" . $fileName,
        ];

        return $res;

    }
}
