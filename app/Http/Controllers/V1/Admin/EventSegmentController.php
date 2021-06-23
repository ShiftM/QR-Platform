<?php

namespace App\Http\Controllers\V1\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\EventSegment;
use App\Http\Requests\Admin\EventSegmentRequest;

class EventSegmentController extends Controller
{

    private $segment;
    public function __construct(EventSegment $segment)
    {
        $this->segment = $segment;
    }

    public function index(){
        $data = EventSegment::get();
        return $this->responseSuccess($data, 200);
    }

    public function store(EventSegmentRequest $request){
        $data = $request->all();
        foreach ($data as $d) {
            $result = $this->segment->create($d);
            $result->segmentExhibitors()->createMany($d['segmentExhibitors']);
        }

        return $this->responseMessage('Successfully Saved!');

        // $rules = [
        //     "event_id" => "required",
        //     "title"    => "required",
        //     "date"     => "required",
        // ];

        // $validator = $this->validator($data, $rules);
        // if ($validator->fails()) {
        //     return $this->responseValidationFail($validator->errors());
        // }


    }

    public function edit($eid){
        $data = EventSegment::where('id', $eid)->first();
        return is_null($data) ? $this->responseFail('No data') : $this->responseSuccess($data);

    }

    public function update(EventSegmentRequest $request, $uid){
        $data = $request->all();
        EventSegment::where('id', $uid)->update($data);
        return $this->responseMessage('Successfully Updated');
    }

    public function delete($did){
        EventSegment::where('id', $did)->delete();
        return $this->responseMessage('Deleted Successfully');
    }
}
