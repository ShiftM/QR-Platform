<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\EventImage;

class EventImagesController extends Controller
{
    public function index(){
        $data = EventImage::get();
        return $this->responseSuccess($data);
    }

    public function store(Request $request){
        $data = $request->all();
        $saveQuest = EventImage::create($data);
        return $this->responseSuccess($saveQuest, 200);
    }

    public function edit($eid){
        $data = EventImage::where('id', $eid)->first();
        return is_null($data) ? $this->responseFail('No data', 200) : $this->responseSuccess($data, 200);

    }

    public function update(Request $request, $uid){
        $data = $request->all();
        EventImage::where('id', $uid)->update($data);
        return $this->responseMessage('Successfully Updated');
    }

    public function delete($did){
        EventImage::where('id', $did)->delete();
        return $this->responseMessage('Deleted Successfully');
    }
}
