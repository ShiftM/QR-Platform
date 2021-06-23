<?php

namespace App\Http\Controllers\V1\Merchant;

use App\User;
use App\UserDeviceId;
use App\UserTransactionHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\EventBooth;
use App\Quest;
use App\Event;
use Dotenv\Regex\Result;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    protected $guard = 'merchant';


    public function getEvents(Request $request){

        $booth = auth($this->guard)->user();
        $data = EventBooth::where('boothId', $booth->id)->with(['event' => function ($query) {
            $query->with(['eventImages' => function($q) {
                $q->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
            }]);
            $query->orderBy('startDate', 'desc');
        }])->get();

        $events = [];
        foreach ($data as $d) {
            if($d['event'] != null) {
                array_push($events, $d['event']);
            }
        }

        return $this->responseSuccess($events, 200);
    }

    public function getQuests(Request $request) {
        
        //check  if deactivate event
        $event = Event::where('id', $request->eventId)->withTrashed()->first();
        // return $event->deleted_at;
        if(!is_null($event->deleted_at)){
            return $this->responseFail(['message' => 'Event not available.', "code" => 422], 422);
        }
        

        $booth = auth($this->guard)->user();
        $quests = Quest::where('eventId', $request->eventId)->where('boothId', $booth->id)->get();
        return $this->responseSuccess($quests, 200);
    }

    public function givePoints(Request $request) {
        $data = $request->all();
        
        $quest = Quest::where('id', $data['questId'])->first();
        $user = User::where('id', $data['userId'])->first();

        //check quest
        if(is_null($quest)){
        return $this->responseFail(['message' => 'Quest no longer exists.', "code" => 422], 422);
        }

        //check  if deactivate event
        $event = Event::where('id', $quest->eventId)->withTrashed()->first();
        // return $event->deleted_at;
        if(!is_null($event->deleted_at)){
            return $this->responseFail(['message' => 'Event not available.', "code" => 422], 422);
        }

        if(!$user) {
            return $this->responseFail(["message" => "User not found!", "code" => 422], 422);
        }
        if(!$quest) {
            return $this->responseFail(["message" => "Quest not found!", "code" => 422], 422);
        }

        $checkExist = UserTransactionHistory::where('userId', $data['userId'])
                                        ->where('questId', $data['questId'])
                                        ->where('action',0)->first();

        if($checkExist) {
                return $this->responseFail(["message" => "Quest already completed. Please select another quest.", "code" => 422], 422);
        }

        $points = $user->points + $quest->points;

        User::where('id', $request->userId)->update([
            "points" => $points
        ]);

        $userUpdatePoints = User::where('id', $request->userId)->first();

        $this->recordEarnedPoints($userUpdatePoints, $quest);
        return $this->responseSuccess(["message" => 'Successfully Credited!'], 200);
    }

    public function recordEarnedPoints($user, $quest) {
        $data = UserTransactionHistory::create([
            "userId" => $user->id,
            "questId" => $quest->id,
            "eventId" => $quest->eventId,
            "redeemedDate" => null,
            "action"    => 0,
            "point"     => $quest->points
        ]);

        $this->userPointsNotif($user);

    }

    public function userPointsNotif($user) {
        //firebase REST url
        $url = 'https://fcm.googleapis.com/fcm/send';

        // firebase app serverKey
        $server_key="AAAAkHR-F-M:APA91bGqTcahMrgnMQ2jdbRGgFsQ0WrF_EvFRN--H4-noTWRRnvNoVcjd3HGwmH6igeyBWT8UbecijFVGQ6QmrT9Ep9cqtXaBiKuMp57hkntFys_3WDzTMOk0htRBkVa-3PEBWoLSxc5";

        $device= UserDeviceId::where('userId', $user->id)->first();

        $fields = [
            "to" => $device->deviceToken,
            "content_available" => true,
            "data" => [
                'points' => $user->points,
                'push_trigger' => 'user_points_update'
            ]
        ];

        $headers = array
        (
            'Authorization: key='.$server_key,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL,  $url );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );

    }
}
