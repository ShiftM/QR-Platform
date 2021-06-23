<?php

namespace App\Http\Controllers\V1_1_0\Merchant;

use App\Booth;
use App\PushNotification;
use App\Repositories\Wallet\WalletRepository;
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

class EventController extends Controller {
	protected $guard = 'merchant';
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var Quest
	 */
	private $quest;
	/**
	 * @var WalletRepository
	 */
	private $walletRepository;
	/**
	 * @var PushNotification
	 */
	private $pushNotification;

	public function __construct(User $user, Quest $quest, WalletRepository $walletRepository, PushNotification $pushNotification) {

		$this->user = $user;
		$this->quest = $quest;
		$this->walletRepository = $walletRepository;
		$this->pushNotification = $pushNotification;
	}

	public function getEvents(Request $request) {

		$booth = auth($this->guard)->user();
		$data = EventBooth::where('boothId', $booth->id)->with(['event' => function ($query) {
			$query->with(['eventImages' => function ($q) {
				$q->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
			}]);
			/*add withTrashed() for temporary solution*/
			$query->withTrashed()->orderBy('startDate', 'desc');
		}])->get();

		$events = [];
		foreach ($data as $d) {
			if ($d['event'] != null) {
				array_push($events, $d['event']);
			}
		}

		return $this->responseSuccess($events, 200);
	}

	public function getQuests(Request $request) {

		//check  if deactivate event;  comment for temporary solution
//		$event = Event::where('id', $request->eventId)->withTrashed()->first();
		// return $event->deleted_at; comment for temporary solution
//		if (!is_null($event->deleted_at)) {
//			return $this->responseFail(['message' => 'Event not available.', "code" => 422], 422);
//		}


		$booth = auth($this->guard)->user();
		$quests = Quest::where('eventId', $request->eventId)->where('boothId', $booth->id)->get();

		return $this->responseSuccess($quests, 200);
	}

	public function givePoints(Request $request) {
		$data = $request->all();

		$user = $this->user->with(['hasOneWalletAccount', 'deviceIds'])->whereCode($data['userId'])->first();
		$quest = $this->quest->with([
			'booth.hasOneWalletAccount'])->find($data['questId']);


		//check quest
		if (!$quest) {
			return $this->responseFail(['message' => 'Quest no longer exists.', "code" => 422], 422);
		}

		//check  if deactivate event comment for temporary solution
//		$event = Event::where('id', $quest->eventId)->withTrashed()->first();
		// return $event->deleted_at; comment for temporary solution
//		if (!is_null($event->deleted_at)) {
//			return $this->responseFail(['message' => 'Event not available.', "code" => 422], 422);
//		}

		if (!$user) {
			return $this->responseFail(["message" => "User not found!", "code" => 422], 422);
		}

		$checkExist = UserTransactionHistory::where('userId', $user->id)
			->where('questId', $data['questId'])
			->where('action', 0)->first();

		if ($checkExist) {
			return $this->responseFail(["message" => "Quest already completed. Please select another quest.", "code" => 422], 422);
		}


		$destination = [
			"account"  => $user->hasOneWalletAccount->account_number,
			"type"     => "DEVICE_ID",
			"currency" => "QOIN",
		];
		$source = [
			"account"  => $quest->booth->hasOneWalletAccount->account_number,
			"type"     => "DEVICE_ID",
			"currency" => "QOIN",
		];

		$wallet = $this->walletRepository->fundTransfer($source, $destination, $quest->points.'00');

		if ($wallet['status'] == "FAILED") {
			return $this->responseFail(["message" => $wallet['message'], "code" => 400], 400);
		} else {
			$balance = $this->walletRepository->balanceInquiry($user->hasOneWalletAccount->account_number);
			foreach ($user->deviceIds as $device) {
				$this->pushNotification->userBalance(json_encode($balance),$device->deviceToken);
			}
			$this->recordEarnedPoints($user->id, $quest);
		}


		return $this->responseSuccess(["message" => 'Successfully Credited!'], 200);
	}

	public function recordEarnedPoints($userId, $quest) {
		$data = UserTransactionHistory::create([
			"userId"       => $userId,
			"questId"      => $quest->id,
			"eventId"      => $quest->eventId,
			"redeemedDate" => null,
			"action"       => 0,
			"point"        => $quest->points,
		]);

	}


}
