<?php

namespace App\Http\Controllers\V1_1_0;

use App\Event;
use App\EventCheckIn;
use App\Helpers\ResponseCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckInController extends Controller {
    //
    protected $guard = 'user';
    private $userId;
    /**
     * @var Event
     */
    private $event;
    /**
     * @var EventCheckIn
     */
    private $eventCheckIn;

    public function __construct(Event $event, EventCheckIn $eventCheckIn) {
//
        $this->middleware('auth:' . $this->guard);
        if ($user = auth($this->guard)->user()) {
            $this->userId = auth($this->guard)->user()->id;
        }
        $this->event = $event;
        $this->eventCheckIn = $eventCheckIn;
    }

    public function store(Request $request) {

        $data = $request->all();
        if($event = $this->event->whereCode($data['id'])->first()){
        	$data['user_id'] = $this->userId;
            $data['event_id'] = $event->id;
	        $response = $this->eventCheckIn->whereUserId($data['user_id'])->whereEventId($data['event_id'])->first();
            if(!$response){
	            $response = $this->eventCheckIn->create($data);
            }

            return $this->responseSuccess($response, ResponseCode::CREATED);
        }
        return $this->responseFailWithCode(ResponseCode::NOT_FOUND);
    }
}
