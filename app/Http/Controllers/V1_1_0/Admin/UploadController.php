<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\Input as input;
use Input;
use Response;
use File;

class UploadController extends Controller
{


    protected $aws;
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    private $uploadToS3;

    public function __construct() {

        $this->uploadToS3 = Storage::disk('s3');
        $this->aws = config('filesystems')['disks']['s3'];
    }

    public function singleUpload(Request $request){
        $data = $request->all();


        // $file_type = ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'];
        $file = Input::file('file');
        $path ="{$this->aws['url']}";
        $name = $file->getClientOriginalName();
        $name = str_replace(' ', '_', $name);
        $extension = $file->getClientOriginalExtension();

        $fileName = $name. '_' . time() . '.'. $extension;

        // if(in_array($extension, $file_type)){
            $this->uploadToS3->put(
                "{$data['upload_path']}/{$fileName}", file_get_contents($file->getRealPath())
            );

            $res = [
                'path'     => "{$path}{$data['upload_path']}/",
                'fileName' => $fileName,
                'fullPath' => "{$path}{$data['upload_path']}/" . $fileName
            ];

            // return $res;
            return $this->responseSuccess($res, 200);
        // }
        // else{
        //     return $this->responseFail(['message' => 'file upload must be jpg, jpeg, png.'], 422);
        // }

    }

    public function multipleUploads(Request $request){
        $data = $request->all();

        $file_type = ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'];
        $path ="{$this->aws['url']}";
        $files = Input::file('file');
        // return $files;
        $photos = [];
        $path ="{$this->aws['url']}";

        $rules = [
            'file.*' => 'image|mimes:jpeg,png,jpg'
        ];

        // $customMessage = [
        //     'file.*' => 'file upload must be jpg, jpeg, png.'
        // ];

        // $validator = $this->validator($data, $rules, $customMessage);
        // if ($validator->fails()) {

        //     return $this->responseValidationFail($validator->errors());
        // }

        foreach($files as $key => $file){

            $name = $file->getClientOriginalName();
            $name = str_replace(' ', '_', $name);
            $extension = $file->getClientOriginalExtension();
            $fileName = $name. '_' . time() . '.'. $extension;

            $this->uploadToS3->put(
                "{$data['upload_path']}/{$fileName}", file_get_contents($file->getRealPath()),
                 'public'
            );

            $photos[$key]['fileName'] =  $fileName;
            $photos[$key]['path'] = "{$path}{$data['upload_path']}/";
            $photos[$key]['fullPath'] = "{$path}{$data['upload_path']}/" . $fileName;

        }

        // return $photos;
        return $this->responseSuccess($photos, 200);
    }
}
