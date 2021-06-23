<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = ['admin_id', 'title', 'message'];

    public function admin()
    {
        return $this->belongsTo('App\Admin');
    }


    public function userBalance($balance,$deviceToken){
	    $url = 'https://fcm.googleapis.com/fcm/send';
	    // firebase app serverKey
	    $server_key = env('FIREBASE_PUSH_NOTIFICATION_SERVER_KEY');


	    $fields = [
		    "to"                => $deviceToken,
		    "content_available" => true,
		    "data"              => [
			    'points'       => $balance,
			    'push_trigger' => 'user_points_update',
		    ],
	    ];

	    $headers = [
		    'Authorization: key=' . $server_key,
		    'Content-Type: application/json',
	    ];

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	    $result = curl_exec($ch);
	    curl_close($ch);
	    return $result;
    }
}
