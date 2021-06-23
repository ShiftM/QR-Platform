<?php

namespace App\Http\Controllers\V1\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Booth;
use App\Http\Requests\Admin\BoothRequest;


class BoothController extends Controller
{
    public function index(){
        $data = Booth::withTrashed()->paginate(25);
        return $this->responseSuccess($data, 200);
    }

    public function store(BoothRequest $request){


        $data = $request->all();
        $data['accountType'] = 0;
        
        // $rules = [
        //     'password' => 'required',
        //     'userName' => 'required|unique:booths',
        // ];

        // $validator = $this->validator($data, $rules);
        // if ($validator->fails()) {
        //     // return response()->json([
        //     //     'message' => 'The given data was invalid.',
        //     //     ''
        //     // ])
        //     return $this->responseValidationFail($validator->errors());
        // }
            
        $data['password'] = bcrypt($request->password);
        $saveBooth = Booth::create($data);
        return $this->responseSuccess($saveBooth, 200);
    }

    public function edit($eid){
        // return Booth::where('id', $eid)->first();
        $data = Booth::withTrashed()->where('id', $eid)->first();
        
        return is_null($data) ? $this->responseFail('No data', 200) : $this->responseSuccess($data, 200);

    }

    public function update(Request $request, $uid){
        $data = $request->all();
        
        $data['accountType'] = 0;
        unset($data['deletedAt']);
        unset($data['createdAt']);
        unset($data['updatedAt']);

        // return $data;

        $rules = [
            'name' => 'required',
            'email' => 'required',
            // 'accountType' => 'required',
            // 'areaCode' => 'required',
            'username' => 'required'
        ];

        $validator = $this->validator($data, $rules);
        if ($validator->fails()) {
            return $this->responseValidationFail($validator->errors());
        }

        Booth::where('id', $uid)->update($data);
        return $this->responseMessage('Successfully Updated');
    }


    public function forgotPassword(Request $request, $id){
        $data = $request->all();
        $password = bcrypt($data['password']);

        $rules = [
           'password' => 'required'
        ];

        $validator = $this->validator($data, $rules);
        if ($validator->fails()) {
            return $this->responseValidationFail($validator->errors());
        }

        Booth::where('id', $id)->update(['password' => $password]);
        return $this->responseMessage('Successfully Updated');
    }


    public function updateStatus($uid) {
        $data = Booth::where('id', $uid)->withTrashed()->first();
        if(!$data->deleted_at) {
            Booth::where('id', $uid)->delete();
        } else {
            Booth::withTrashed()->find($uid)->restore();
        }
    }

    public function delete($did){
        Booth::where('id', $did)->delete();
        return $this->responseMessage('Deleted Successfully');
    }
}
