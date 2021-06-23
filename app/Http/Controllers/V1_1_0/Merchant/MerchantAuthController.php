<?php

namespace App\Http\Controllers\V1_1_0\Merchant;

use App\Booth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MerchantAuthController extends Controller
{
    protected $guard = 'merchant';

    public function __construct() {
        $this->middleware('auth:'. $this->guard, ['except' => ['login']]);
    }

    public function login() {
        // get email and password from request
        $credentials = request(['username', 'password']);
        // try to auth and get the token using api authentication

        $data = Booth::where('username', $credentials['username'])->withTrashed()->first();
        if($data && $data->deleted_at) {
            return $this->responseFail(["code" => 401, "message" => "Failed to login, contact support@questrewards.com"], 401);
        }
        if (!$token = auth($this->guard)->attempt($credentials)) {
            // if the credentials are wrong we send an unauthorized error in json format
            return $this->responseFail(["code" => 401, "message" => "Invalid Credentials"], 401);
        }

        return $this->responseSuccess([
            'token' => $token,
            'type' => 'bearer', // you can ommit this
            'expires' => auth($this->guard)->factory()->getTTL() * 3600, // time to expiration
        ], 200);
    }

    public function profile() {
        return $this->responseSuccess(auth($this->guard)->user(), 200);
    }

    public function logout() {
        auth($this->guard)->logout();
        return $this->responseSuccess(["message" => 'Successfully Logged out!'], 200);
    }
}
