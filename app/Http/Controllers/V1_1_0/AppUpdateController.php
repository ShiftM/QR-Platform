<?php

namespace App\Http\Controllers\V1_1_0;

use App\AppUpdate;
use App\Helpers\ResponseCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppUpdateController extends Controller {
    //

    /**
     * @var AppUpdate
     */
    private $appUpdate;

    public function __construct(AppUpdate $appUpdate){

        $this->appUpdate = $appUpdate;
    }

    public function validateVersion(Request $request) {
        $data = $request->all();
        $query = $this->appUpdate->latest('id')->first();
        $response = [
            "force_update" => false
        ];
        if($query->version > $data['version']){
            $response['force_update'] = true;
        }
        return $this->responseSuccess($response, ResponseCode::OKAY);
    }
}
