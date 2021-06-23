<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    protected $guard = 'admin';

    public function __construct() {
        $this->middleware('auth:'. $this->guard, ['except' => ['login']]);
    }

    public function login() {
        // get email and password from request
        $credentials = request(['email', 'password']);
        $rules = [
            'password' => 'required',
            'email' => 'required',
        ];

        $validator = $this->validator($credentials, $rules);
        if ($validator->fails()) {
            return $this->responseValidationFail($validator->errors());
       }

        // try to auth and get the token using api authentication
        if (!$token = auth($this->guard)->attempt($credentials)) {
            // if the credentials are wrong we send an unauthorized error in json format
            return $this->responseFail(["code" => 401, "message" => "Invalid Credentials"], 401);
            // return $this->responseFailWithCode(401);
        }
        return $this->responseSuccess([
            'token' => $token,
            'type' => 'bearer', // you can ommit this
            'expires' => auth($this->guard)->factory()->getTTL() * 31536000, // time to expiration
        ], 200);
    }

    public function profile() {

        return $this->responseSuccess(auth($this->guard)->user(), 200);
    }

    public function refresh()
    {

        $response = [
            'access_token' => auth($this->guard)->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth($this->guard)->factory()->getTTL() * 1000000000
        ];

        return $this->showResponse($response);
    }


    public function logout() {
        auth($this->guard)->logout();
        return $this->responseSuccess(["message" => 'Successfully Logged out!'], 200);
    }
}
