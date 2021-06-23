<?php

namespace App\Http\Controllers\V1\Admin;

use App\EventDay;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\EventSchedule;
use App\Http\Requests\Admin\EventScheduleRequest;

class EventSchedulesController extends Controller
{
    private $day;
    public function __construct(EventDay $day)
    {
        $this->day = $day;
    }

    public function index(){
        $data = EventSchedule::get();
        return $this->responseSuccess($data, 200);
    }

    public function store(Request $request){

        $data = $request->all();
        foreach ($data as $d) {
            $result = $this->day->create($d);
            $result->schedules()->createMany($d['schedules']);
        }

        return $this->responseSuccess(["message" => "Successfully Saved!"], 200);
    }

    public function edit($eid){
        $data = EventSchedule::where('id', $eid)->first();
        return is_null($data) ? $this->responseFail('No data', 200) : $this->responseSuccess($data, 200);

    }

    public function update(EventScheduleRequest $request, $uid){
        $data = $request->all();
        EventSchedule::where('id', $uid)->update($data);
        return $this->responseMessage('Successfully Updated');
    }

    public function delete($did){
        EventSchedule::where('id', $did)->delete();
        return $this->responseMessage('Deleted Successfully');
    }
}
