<?php

namespace App\Http\Controllers\V1\Admin;

use App\Event;
use App\User;
use App\UserDeviceId;
use App\UserTransactionHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Quest;
use App\Http\Requests\Admin\QuestRequest;
use Dotenv\Regex\Result;

class QuestController extends Controller
{
    public function index(Request $request){
        $data = new Quest;
        if($request->boothId){
            $data = $data->with(['event' => function($q){
                $q->withTrashed();
            }])->where('boothId', $request->boothId);
        }
        $data = $data->paginate(10);
        return $this->responseSuccess($data, 200);
    }

    public function store(QuestRequest $request){
        $data = $request->all();
        Quest::create($data);
        return $this->responseMessage('Successfully Saved!');
    }

    public function edit($eid){
        $data = Quest::where('id', $eid)->first();
        return is_null($data) ? $this->responseFail('No data', 200) : $this->responseSuccess($data, 200);

    }

    public function update(QuestRequest $request, $uid){
        $data = $request->all();
        unset($data['createdAt']);
        unset($data['updatedAt']);
        unset($data['deletedAt']);
        Quest::where('id', $uid)->update($data);
        return $this->responseMessage('Successfully Updated');
    }

    public function delete($did){
        Quest::where('id', $did)->delete();
        return $this->responseMessage('Deleted Successfully');
    }

    public function redeemPoints(Request $request) {
        $data = $request->all();
        $user = User::where('id',$data['userId'])->first();
        $event = Event::where('id', $data['eventId'])->first();

        if(!$user) {
            return $this->responseFail(["message" => "User not found!", "code" => 422], 422);
        }
        if(!$event) {
            return $this->responseFail(["message" => "Event not found!", "code" => 422], 422);
        }


        if($data['points'] > $user->points) {
            return $this->responseFail(["message" => "Cannot redeem points. User has insufficient points.", "code" => 422], 422);
        }

        $points = $user->points - $data['points'];
        User::where('id',$data['userId'])->update([
            "points" => $points
        ]);

        $user = User::where('id',$data['userId'])->first();    

        $this->recordRedeemedPoints($user, $data);
        return $this->responseSuccess(["message" => "Successfully Redeemed!"], 200);
    }

    public function recordRedeemedPoints($user, $data) {
        UserTransactionHistory::create([
            "userId" => $data['userId'],
            "questId" => 0,
            "eventId" => $data['eventId'],
            "redeemedDate" => date('Y-m-d H:i:s'),
            "itemName" => $data['itemName'],
            "action"    => 1,
            "point"     => $data['points']
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

    public function mostViewed($user, $data) {

        // RETURNS ALL QUESTS

        
        // $data = new Quest;
        // if($request->boothId){
        //     $data = $data->with(['event' => function($q){
        //         $q->withTrashed();
        //     }])->where('boothId', $request->boothId);
        // }
        // $data = $data->paginate(10);
        return $this->responseSuccess($data, 200);
    }
    public function numberOfFinished($user, $data) {
        // $data = new Quest;
        // if($request->boothId){
        //     $data = $data->with(['event' => function($q){
        //         $q->withTrashed();
        //     }])->where('boothId', $request->boothId);
        // }
        // $data = $data->paginate(10);
        return $this->responseSuccess($data, 200);
    }
}
