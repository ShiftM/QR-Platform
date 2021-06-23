<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Booth;
use App\EventDay;
use App\EventSchedule;
use App\EventSegment;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Event;
use App\Http\Requests\Admin\EventRequest;
use Aws\Result;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{

    /**
     * @var Event
     */
    private $event;
    /**
     * @var EventDay
     */
    private $eventDay;
    /**
     * @var EventSegment
     */
    private $eventSegment;
	/**
	 * @var WalletRepository
	 */
	private $walletRepository;

	public function __construct(Event $event, EventDay $eventDay, EventSegment $eventSegment,WalletRepository $walletRepository)
    {

        $this->event = $event;
        $this->eventDay = $eventDay;
        $this->eventSegment = $eventSegment;
	    $this->walletRepository = $walletRepository;
    }

    public function createRelatedData($event, $data) {
        $event->organizer()->create($data['organizer']);

        foreach ($data['eventDays'] as $key => $value) {
            $value['date'] = date('Y-m-d H:i:s', strtotime($value['date']));
            $res = $event->eventDays()->create($value);
            foreach ($value['schedules'] as $sched) {
                $sched['time'] = date_format(date_create($res->date), "Y-m-d") . ' ' . date('H:i:s', strtotime($sched['time']));
                $res->schedules()->create($sched);
            }

        }

        foreach ($data['segments'] as $s) {
            $seg = $event->segments()->create($s);
            $seg->segmentExhibitors()->createMany($s['segmentExhibitors']);
            $seg->locationImages()->createMany($s['locationImages']);
        }

        foreach ($data['eventImages'] as $s) {
            $seg = $event->eventImages()->create($s);
        }
    }

    public function index(Request $request){
	    $details = $request->all();
        $data = Event::select('events.*', 'event_organizers.name')
        ->leftjoin('event_organizers','event_organizers.eventId','events.id')
        ->withTrashed();

        if ((isset($details['date_from']) && $details['date_from']) && (isset($details['date_to']) && $details['date_to'])) {
            $data = $data->whereBetween('startDate', [date('y-m-d H:i:s', strtotime($details['date_from'])),
                date('y-m-d h:i:s', strtotime($details['date_to']))]);
        }
        if (isset($details['name']) && $details['name']) {
            $data = $data->where(function($query)  use($details){
                $query->where('name', 'like' ,'%'.$details['name'].'%')
                    ->orWhere('title', 'like' ,'%'.$details['name'].'%');
            });
        }

        $data = $data->orderBy('startDate', 'desc')->distinct();
        $data = json_decode($details['paginate']) ? $data->paginate($details['per_page']) : $data->get();

       //get present and past date
        $present = [];
        $past = [];
        foreach($data as $key => $value){
            if(date('Y-m-d H:i:s', strtotime($value->startDate)) >= date('Y-m-d H:i:s')){
                array_push($present, $value);
            }
            if(date('Y-m-d H:i:s', strtotime($value->startDate)) < date('Y-m-d H:i:s')){
                array_push($past, $value);
            }
        }

        usort($present, function($a, $b)
            {
                return $a->startDate > $b->startDate;
            });

            $merge_data =  array_merge($present, $past);

            foreach($merge_data as $mkey => $mvalue){
                    $data[$mkey] = $mvalue;
            }

        return $this->responseSuccess($data, 200);
    }


    public function store(EventRequest $request){

        $data = $request->all();
        $data['startDate'] = date('Y-m-d H:i:s', strtotime($data['startDate']));
        $data['startTime'] = date('Y-m-d H:i:s', strtotime($data['startTime']));
        $data['endDate'] = date('Y-m-d H:i:s', strtotime($data['endDate']));
        $data['endTime'] = date('Y-m-d H:i:s', strtotime($data['endTime']));

        usort($data['eventDays'], function($a, $b)
            {
                return strtotime($b['date']) < strtotime($a['date']);
            });
        // $rules = [
        //     "startDate" => "after:yesterday",
        // ];

        // $validator = $this->validator($data, $rules);
        // if ($validator->fails()) {
        //     return $this->responseValidationFail($validator->errors());
        // }
        $result = $this->event->create($data);
	    // $wallet = $this->walletRepository->createAccount('event');
	    // $wallet_input = [
		//     "table_type"=>"events",
		//     "table_id" => $result->id,
		//     "account_identifier" => "device_id",
		//     "account_number" => $wallet['deviceId'],
	    // ];

	    // $this->walletRepository->storeAccount($wallet_input);
        $this->createRelatedData($result, $data);

        //set as deactivate event
        $this->event->where('id', $result->id)->delete();

        return $this->responseSuccess($result, 200);
    }

    public function edit($eid){
        $data = Event::where('id', $eid)->first();
        return is_null($data) ? $this->responseFail(["code" => 401, "message" => "Invalid Credentials"], 500) : $this->responseSuccess($data, 200);

    }

    public function update(EventRequest $request, $uid){
        $data = $request->all();

        $data['startDate'] = str_replace(str_split('TZ'), ' ', $data['startDate']);
        $data['endDate'] = str_replace(str_split('TZ'), ' ', $data['endDate']);
        $data['startTime'] =  $data['startDate'];
        $data['endTime'] = $data['endDate'];

        $data['startDate'] = date('Y-m-d H:i:s', strtotime($data['startDate']));
        $data['startTime'] = date('Y-m-d H:i:s', strtotime($data['startTime']));
        $data['endDate'] = date('Y-m-d H:i:s', strtotime($data['endDate']));
        $data['endTime'] = date('Y-m-d H:i:s', strtotime($data['endTime']));


        usort($data['eventDays'], function($a, $b)
            {
                return strtotime($b['date']) < strtotime($a['date']);
            });

        foreach ($data['eventDays'] as $key => $value) {
            $data['eventDays'][$key]['date'] = str_replace(str_split('TZ'), ' ', $value['date']);
        }
        //  return $data;
        $event = $this->event->withTrashed()->find($uid);
        // return $event;
        $event->fill($data)->save();
        $event->deleteRelatedData();
        $this->createRelatedData($event, $data);

        return $this->responseSuccess(["message" => "Successfully Updated"], 200);

    }

    public function delete($did){
        Event::where('id', $did)->delete();
        return $this->responseSuccess(["message" => "Successfully Deleted"], 200);

    }

    public function getBooths() {
        $data = Booth::get();
        return $this->responseSuccess($data, 200);
    }

    public function info($id) {
        $data = Event::where('id', $id)->withTrashed()->with(['organizer','eventDays' => function($query){
            $query->with(['schedules' => function($q){
                $q->orderBy('time', 'asc');
            }]);
        }, 'segments', 'eventImages' => function($query) {
            $query->select('eventId', 'path', 'fileName', DB::raw("CONCAT(path, fileName) as fullPath"));
        }])->first();
        return $this->responseSuccess($data, 200);
    }

    public function getEventsApp() {
        $event = $this->event->with(['eventImages' => function ($query) {
            $query->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
        }])->get();
        return $this->responseSuccess($event, 200);
    }

    public function updateStatus($uid) {
        $data = $this->event->where('id', $uid)->withTrashed()->first();
        if(!$data->deleted_at) {
            $this->event->where('id', $uid)->delete();
        } else {
            $this->event->withTrashed()->find($uid)->restore();
        }
    }
}
