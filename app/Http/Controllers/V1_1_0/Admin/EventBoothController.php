<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Quest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\EventBooth;
use App\Http\Requests\Admin\EventBoothRequest;

class EventBoothController extends Controller
{
    public function index(Request $request){

        $data = EventBooth::with(['event' => function($q){
            $q->withTrashed();
        }, 'booth']);
        if($request->boothId){
            $data = $data->where('boothId', $request->boothId);
        }
        $data = $data->get();
        return $this->responseSuccess($data, 200);
    }

    public function store(EventBoothRequest $request){
        $data = $request->all();

        // $rules = [
        //     'event_id' => 'required',
        //     'booth_id' => 'required'
        // ];

        // $validator = $this->validator($data, $rules);
        // if ($validator->fails()) {
        //     return $this->responseValidationFail($validator->errors());
        // }

        $check = EventBooth::where('eventId', $data['eventId'])->where('boothId', $data['boothId'])->first();
        if(!$check) {
            EventBooth::create($data);
            return $this->getEventBooth($data['eventId']);
        } else {
            return $this->responseFail(["message" => "Booth was already assigned to the event."], 500);
        }
    }

    public function edit($eid){
        $data = EventBooth::where('id', $eid)->first();
        return is_null($data) ? $this->responseFail('No data', 200) : $this->responseSuccess($data, 200);

    }

    public function update(EventBoothRequest $request, $uid){
        $data = $request->all();

        // $rules = [
        //     'event_id' => 'required',
        //     'booth_id' => 'required'
        // ];

        // $validator = $this->validator($data, $rules);
        // if ($validator->fails()) {
        //     return $this->responseValidationFail($validator->errors());
        // }

        EventBooth::where('id', $uid)->update($data);
        return $this->responseMessage('Successfully Updated');
    }

    public function delete(Request $request){
        $eventBooth = EventBooth::find($request->id);
        Quest::where('boothId', $eventBooth->boothId)->where('eventId', $eventBooth->eventId)->delete();
        $eventBooth->delete();
        return $this->getEventBooth($request->event_id);
    }

    public function getEventBooth($id) {
        $data = EventBooth::with(['event' => function($q){
          $q->withTrashed();
        }, 'booth'])->where('eventId', $id)->paginate(10);
        return $this->responseSuccess($data, 200);
    }
}
