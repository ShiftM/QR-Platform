<?php

namespace App\Http\Controllers\V1_1_0;

use App\Helpers\ResponseCode;
use App\Helpers\ResponseFormatter;
use App\Helpers\Status;
use App\OrderHeaderTemp;
use App\Repositories\Order\OrderRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class CheckoutController extends Controller {
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


	public function __construct(OrderRepository $orderRepository, OrderHeaderTemp $orderHeaderTemp, ResponseFormatter $responseFormatter) {

		$this->middleware('jwt.auth:' . $this->guard);

		$this->userId = auth($this->guard)->user()->id;
		$this->orderRepository = $orderRepository;
		$this->orderHeaderTemp = $orderHeaderTemp;
		$this->responseFormatter = $responseFormatter;
	}


	public function postOrder(Request $request) {

		$content = $this->orderRepository->content($this->userId);
		$response = $this->orderHeaderTemp->create($content);
		$response->hasManyOrderDetailTemp()->createMany($content['details']);
		$response->hasManyOrderTotal()->createMany($content['totals']);

		$response = json_decode($this->getOrder()->content(),true);


		return $this->responseSuccess($response['data'], ResponseCode::OKAY);
	}

	public function getOrder() {

		$response = $this->orderHeaderTemp
            ->withCount(['hasManyOrderDetailTemp as total_quantity' => function ($query) {
                $query->select(DB::raw("SUM(quantity)"));
            }])->withRelatedModels()
            ->whereUserId($this->userId)
            ->orderBy('id', 'DESC')
            ->first()
            ->toArray();


        $arrays = [];
        if ($response) {
            array_push($arrays, $this->responseFormatter->toOrder($response));
		}

		return $this->responseSuccess($arrays[0], ResponseCode::OKAY);
	}

}
