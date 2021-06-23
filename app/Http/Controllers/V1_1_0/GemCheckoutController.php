<?php

namespace App\Http\Controllers\V1_1_0;

use App\GemOrderHeaderTemp;
use App\GemPackage;
use App\Helpers\ResponseCode;
use App\Helpers\ResponseFormatter;
use App\Helpers\Status;
use App\Http\Requests\GemCheckoutRequest;
use App\OrderHeaderTemp;
use App\Repositories\Order\OrderRepository;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class GemCheckoutController extends Controller {
	//
	protected $guard = 'user';
	/**
	 * @var int
	 */
	private $userId;
	/**
	 * @var OrderRepository
	 */
	private $orderRepository;
	/**
	 * @var OrderHeaderTemp
	 */
	private $orderHeaderTemp;
	/**
	 * @var ResponseFormatter
	 */
	private $responseFormatter;
	/**
	 * @var GemOrderHeaderTemp
	 */
	private $gemOrderHeaderTemp;
	/**
	 * @var GemPackage
	 */
	private $gemPackage;
	/**
	 * @var User
	 */
	private $user;


	public function __construct(GemOrderHeaderTemp $gemOrderHeaderTemp, GemPackage $gemPackage, User $user) {


		$this->gemOrderHeaderTemp = $gemOrderHeaderTemp;
		$this->gemPackage = $gemPackage;
		$this->user = $user;
	}


	public function postOrder(GemCheckoutRequest $request) {

		$data = $request->all();

		$data['user_id'] = $this->getUserId($data['country_code'], $data['phone_number']);

		$content = $this->getContent($data['gem_package_id']);

		$response = $this->gemOrderHeaderTemp->create($data);
		$response->hasOneGemOrderDetailTemp()->create($content['details']);
		$response->hasOneOrderTotal()->create($content['totals']);
		$response = $this->gemOrderHeaderTemp->with(['hasOneGemOrderDetailTemp', 'hasOneOrderTotal'])->whereUserId($data['user_id'])->orderBy('id', 'DESC')->first()->toArray();

		return $this->responseSuccess($response, ResponseCode::OKAY);
	}

	public function getOrder(Request $request) {
		$data = $request->all();
		$data['user_id'] = $this->getUserId($data['country_code'], $data['phone_number']);
		$response = $this->gemOrderHeaderTemp->with(['hasOneGemOrderDetailTemp', 'hasOneOrderTotal'])->whereUserId($data['user_id'])->orderBy('id', 'DESC')->first()->toArray();

		return $this->responseSuccess($response, ResponseCode::OKAY);
	}


	public function getContent($id) {

		$data = $this->gemPackage->find($id);

		$inputs['details'] = [
			'gem_package_name'   => $data->name,
			'gem_package_amount' => $data->amount,
			'gem_package_price'  => $data->price,
			'gem_package_id'     => $data->id,
		];

		$inputs['totals'] = [
			"currency_type_id" => 1,
			"sub_total"        => $data->price,
			"shipping_fee"     => 0,
			"discount"         => 0,
			"grand_total"      => $data->price,
		];

		return $inputs;
	}

	public function getUserId($countryCode, $phoneNumber) {

		$response = $this->user
			->where('phoneNumber', $phoneNumber)
			->where('countryCode', $countryCode)
			->first();

		return $response->id;
	}

}
