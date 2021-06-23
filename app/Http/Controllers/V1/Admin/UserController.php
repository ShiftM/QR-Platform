<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller {

    public function index() {
        $data = User::withTrashed()->orderBy('id', 'desc')->paginate(25);
        return $this->responseSuccess($data, 200);
    }


    public function edit($uid) {
        $data = User::withTrashed()->where('id', $uid)->first();
        // return $data;
        return is_null($data) ? $this->responseFail(["code" => 422, "message" => "No data found"], 422) : $this->responseSuccess($data, 200);
    }

    public function update(Request $request, $uid) {
        $data = $request->all();
        unset($data['createdAt']);
        unset($data['updatedAt']);
        unset($data['deletedAt']);

        $rules = [
            'phoneNumber' => 'required|numeric|digits:10',
            'username' => 'required',
            'points' => 'required|numeric|regex:/^[0-9]*$/'
        ];

        $validator = $this->validator($data, $rules);
        if ($validator->fails()) {
            return $this->responseValidationFail($validator->errors());
        }

        User::where('id', $uid)->update($data);
        $updated = User::where('id', $uid)->first();
        return $this->responseSuccess($updated, 200);
    }

    public function updateStatus($uid) {
        $data = User::where('id', $uid)->withTrashed()->first();
        if(!$data->deleted_at) {
            User::where('id', $uid)->delete();
        } else {
            User::withTrashed()->find($uid)->restore();
        }
    }
}
