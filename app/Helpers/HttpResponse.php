<?php
use App\Helpers\ResponseCode as ResponseCode;
function showResponse($data) {


    $response = [
        'code'   => ResponseCode::OKAY,
        'success' => true,
        'data'   => $data,
    ];

    return response()->json($response, $response['code']);
}


function listResponse($data) {
    $response = [
        'code'   => ResponseCode::OKAY,
        'success' => true,
        'data'   => $data,
    ];

    return response()->json($response, $response['code']);
}

function createdResponse($data) {


    $response = [
        'code'   => ResponseCode::CREATED,
        'success' => true,
        'data'   => $data,
    ];

    return response()->json($response, $response['code']);
}

function notFoundResponse() {


    $response = [
        'code'    => ResponseCode::NOT_FOUND,
        'success'  => false,
        'data'    => 'Resource Not Found',
        'message' => 'Not Found',
    ];

    return response()->json($response, $response['code']);
}


function deletedResponse() {


    $response = [
        'code'    => ResponseCode::NO_CONTENT,
        'success'  => true,
        'data'    => [],
        'message' => 'Resource deleted',
    ];

    return response()->json($response, $response['code']);
}


function clientErrorResponse(array $validation = [], $code) {


    $response = [
        'code'    => $code,
        'success'  => false,
        'data'    => null,
        'errors' => $validation,
        'message' => 'Unprocessable entity',
    ];

    return response()->json($response, $response['code']);
}

function unauthorizedResponse() {


    $response = [
        'code'    => ResponseCode::UNAUTHORIZED,
        'success'  => false,
        'data'    => [],
        'message' => 'Unauthorized',
    ];

    return response($response, 401);
}


function customResponse($code, $message,array $data, $status) {
    $response = [
        'code'    => $code,
        'success'  => $status,
        'data'    => $data,
        'message' => $message,
    ];

    return response($response,$code);
}
