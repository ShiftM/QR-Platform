<?php

namespace App\Http\Controllers\V1_1_0;

use App\GemOrderHeader;
use App\GemOrderHeaderTemp;
use App\Helpers\ResponseCode;
use App\Helpers\Status;
use App\Http\Requests\Admin\IndexRequest;
use App\PushNotification;
use App\Repositories\Wallet\WalletRepository;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GemOrderController extends Controller {

	protected $guard = 'user';
	/**
	 * @var GemOrderHeader
	 */
	private $gemOrderHeader;
	private $userId;
	/**
	 * @var GemOrderHeaderTemp
	 */
	private $gemOrderHeaderTemp;
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var WalletRepository
	 */
	private $walletRepository;
	/**
	 * @var PushNotification
	 */
	private $pushNotification;


	public function __construct(GemOrderHeader $gemOrderHeader, GemOrderHeaderTemp $gemOrderHeaderTemp, User $user, WalletRepository $walletRepository, PushNotification $pushNotification) {

		$this->middleware('auth:' . $this->guard)->except(['store', 'show']);


		if (auth($this->guard)->user()) {
			$this->userId = auth($this->guard)->user()->id;
		}


		$this->gemOrderHeader = $gemOrderHeader;
		$this->gemOrderHeaderTemp = $gemOrderHeaderTemp;
		$this->user = $user;
		$this->walletRepository = $walletRepository;
		$this->pushNotification = $pushNotification;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(IndexRequest $request) {
		//

		$data = $request->all();

		$response = $this->gemOrderHeader
			->withRelatedModels()
			->whereUserId($this->userId);

		if (isset($data['order_number']) && $data['order_number']) {
			$response = $response->where('order_number', 'like', '%' . $data['order_number'] . '%');
		}

		if (isset($data['status_option_id']) && $data['status_option_id']) {
			$response = $response->whereStatusOptionId($data['status_option_id']);
		}


		$response = $response->orderBy('id', 'DESC');

		$response = json_decode($data['paginate']) ? $response->paginate($data['per_page'])->toArray() : $response->get()->toArray();
		if (!json_decode($data['paginate'])) {

			return $this->responseSuccess($response, ResponseCode::OKAY);
		} else {

			return $this->responseSuccessWithPagination($response, ResponseCode::OKAY);
		}

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		//

		$data = $request->all();


		$temp = $this->gemOrderHeaderTemp
			->with(['hasOneGemOrderDetailTemp', 'hasOneOrderTotal'])
			->whereId($data['order_id'])->whereOrderNumber($data['order_number'])->first()->toArray();


		$temp['status_option_id'] = $data['status_option_id'];

		$response = $this->gemOrderHeader->create($temp);


//
		if ($data['order_shipping']) {

			$response->hasOneOrderShipping()->create($data['order_shipping']);
		}

//		if (isset($data['payment_detail']) && $data['payment_detail']) {
//			$data['payment_detail']["status_option_id"] = Status::PAID;
//			$response->hasOnePaymentDetail()->create($data['payment_detail']);
//		}

		$response->hasOneOrderTotal()->create($temp['has_one_order_total']);
		$response->hasOneGemOrderDetail()->create($temp['has_one_gem_order_detail_temp']);


		if (isset($data['order_recipient']) && $data['order_recipient'] ) {
			$user = $this->user->with(['hasOneWalletAccount','deviceIds'])->where('phoneNumber', $data['order_recipient']['phone_number'])->first();
			$data['order_recipient']['full_name'] = $user->username;
			$response->hasOneOrderRecipient()->create($data['order_recipient']);
//			if($data['status_option_id'] == Status::COMPLETED){
//				$this->givePoints($user->hasOneWalletAccount->account_number, $temp['has_one_gem_order_detail_temp']['gem_package_amount'].'00');
//				$balance = $this->walletRepository->balanceInquiry($user->hasOneWalletAccount->account_number);
//				foreach ($user->deviceIds as $device) {
//					$this->pushNotification->userBalance(json_encode($balance),$device->deviceToken);
//				}
//			}

		}

		return $this->responseSuccess(["status" => "success","id"=>$response->id], ResponseCode::OKAY);
	}

	public function givePoints($destination_account, $amount) {

		$source = [
			"account"  => config('env.wallet_merchant_gem_account'),
			"type"     => "ACCOUNT_NUMBER",
			"currency" => "GEM",
		];
		$destination = [
			"account"  => $destination_account,
			"type"     => "DEVICE_ID",
			"currency" => "GEM",
		];


		$this->walletRepository->fundTransfer($source, $destination, $amount);

	}


	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
		$response = $this->gemOrderHeader
			->withRelatedModels()
			->find($id);

		return $this->responseSuccess($response, ResponseCode::OKAY);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		//

		$data = $request->all();

		if ($response = $this->gemOrderHeader->find($id)) {
			$response->fill($data)->save();

			$response = json_decode($this->show($response->id)->content(), true);

			return $this->responseSuccess($response['data'], ResponseCode::OKAY);

		}

		return $this->responseFailWithCode(ResponseCode::NOT_FOUND);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		//
	}
}
