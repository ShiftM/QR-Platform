<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function validator($data, $rules, $messages = []) {

        return \Validator::make($data, $rules, $messages);
    }


    protected function exceptionHandler($callback){
        try {

            return $callback();

        } catch (\Exception $ex) {

        	return $ex;
            dd($ex);
//            return clientErrorResponse([], [
//                "message" => $ex->getMessage(),
//                "line"    => $ex->getLine(),
//            ]);

        }

    }


	public function responseSuccessWithPagination($data, $code) {
		$response = [
			"success" => true,
			"data"    => $data['data'],
			"error"   => null,
			"pagination" => [
				"current_page" => $data['current_page'],
				"total" => $data['total'],
				"per_page" => intval($data['per_page']),
				"from" => $data['from'],
				"last_page" => $data['last_page'],
			]
		];

		return response()->json($response, $code);
	}

    public function responseSuccess($data, $code) {
        $response = [
            "success" => true,
            "data"    => $data,
            "error"   => null
        ];

        return response()->json($response, $code);
    }

    public function responseFailWithCode($code) {
        switch ($code) {
            case 401:
                $data = [
                    'message' => 'Unauthorized',
                    'code' => 401
                ];
                break;
            case 404:
                $data = [
                    'message' => 'Not Found',
                    'code' => 404
                ];
                break;
            case 500:
                $data = [
                    'message' => 'Unexpected Internal Server Error',
                    'code' => 500
                ];
                break;
            case 555:
                $data = [
                    'message' => 'Authentication token has expired.',
                    'code' => 401
                ];
                break;
            case 600:
                $data = [
                    'message' => 'POST body(JSON) parameter invalid',
                    'code' => 422
                ];
                break;
            case 700:
                $data = [
//                    'message' => 'Mobile number does not exist',
                    'message' => 'You are not yet registered',
                    'code' => 403
                ];
                break;
            case 701:
                $data = [
                    'message' => 'OTP has expired',
                    'code' => 403
                ];
                break;
            case 702:
                $data = [
                    'message' => 'OTP does not match',
                    'code' => 403
                ];
                break;
            case 703:
                $data = [
                    'message' => 'Reached max number of OTP sent for today',
                    'code' => 401
                ];
                break;
            case 710:
                $data = [
                    'message' => 'User not found',
                    'code' => 404
                ];
                break;
            case 711:
                $data = [
//                    'message' => 'Phone Number is used',
                    'message' => 'You are already registered',
                    'code' => 422
                ];
                break;
            case 712:
                $data = [
                    'message' => 'OTP failed. Please try again',
                    'code' => 403
                ];
                break;
            case 713:
                $data = [
                    'message' => 'Invalid phone number',
                    'code' => 422
                ];
                break;
            case 714:
                $data = [
                    'message' => 'Username is already taken',
                    'code' => 422
                ];
                break;
        }

        $response = [
            "success" => false,
            "data"    => null,
            "error"   => $data
        ];

        return response()->json($response, $data['code']);
    }

    public function responseFail($data, $code) {
        $response = [
            "success" => false,
            "data"    => null,
            "error"   => $data
        ];

        return response()->json($response, $code);
    }

    public function responseValidationFail($data) {
        $response = [
            'success'  => false,
            'errors' => $data,
            'message' => 'Unprocessable entity',
        ];

        return response()->json($response, 422);
    }

    public function responseMessage($message){
        $response = [
            "success" => true,
            "message" => ["data" => $message],
            "error"   => null
        ];

        return response()->json($response);
    }

    public function camelCaseKeys($collection) {
        $changedCollection = [];
        foreach ($collection as $index => $value) {
            $index = $this->fromSnakeCase($index);
            $changedCollection[$index] = $value;
        }

        return $changedCollection;
    }

    public function snakeCaseKeys($collection) {
        $changedCollection = [];

        foreach ($collection as $index => $value) {
            $index = $this->fromCamelCase($index);
            $changedCollection[$index] = $value;
        }

        return $changedCollection;
    }

    private function fromSnakeCase($input) {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    private function fromCamelCase($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

}
