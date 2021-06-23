<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\EventCompany;
use App\Http\Requests\Admin\EventCompanyRequest;

class EventCompanyController extends Controller
{
    public function index(Request $request){

        $data = EventCompany::with(['event' => function($q){
            $q->withTrashed();
        }, 'company']);
        if($request->companyId){
            $data = $data->where('companyId', $request->companyId);
        }
        $data = $data->get();
        return $this->responseSuccess($data, 200);
    }

    public function store(EventCompanyRequest $request){
        $data = $request->all();

        $check = EventCompany::where('eventId', $data['eventId'])->where('companyId', $data['companyId'])->first();
        if(!$check) {
            EventCompany::create($data);
            return $this->getEventCompany($data['eventId']);
        } else {
            return $this->responseFail(["message" => "Company was already assigned to the event."], 500);
        }
    }

    public function edit($eid){
        $data = EventCompany::where('id', $eid)->first();
        return is_null($data) ? $this->responseFail('No data', 200) : $this->responseSuccess($data, 200);
        
    }

    public function update(EventCompanyRequest $request, $uid){
        $data = $request->all();

        EventBooth::where('id', $uid)->update($data);
        return $this->responseMessage('Successfully Updated');
    }

    public function delete(Request $request){
        EventCompany::where('id', $request->id)->delete();
        return $this->getEventCompany($request->event_id);
    }

    public function getEventCompany($id) {
        $data = EventCompany::with(['event' => function($q){
          $q->withTrashed();  
        }, 'company'])->where('eventId', $id)->paginate(10);
        return $this->responseSuccess($data, 200);
    }
}
