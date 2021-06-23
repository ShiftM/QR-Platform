<?php

namespace App\Http\Controllers\V1_1_0;

use App\PushNotification;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    //

	/**
	 * @var PushNotification
	 */
	private $pushNotification;
	/**
	 * @var User
	 */
	private $user;

	public function __construct(PushNotification $pushNotification, User $user){

		$this->pushNotification = $pushNotification;
		$this->user = $user;
	}

	public function pushNotification(Request $request){
		$data = $request->all();

		$response = $this->user->with('deviceIds')->where('phoneNumber',$data['number'])->first();

		foreach ($response->deviceIds as $key => $v){
			$result = $this->pushNotification->userBalance('test',$v->deviceToken);
		}
		return $result;
	}
}
