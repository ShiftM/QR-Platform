<?php

namespace App\Http\Controllers\V1_1_0;

use App\Calculator;
use App\CartDetail;
use App\CartHeader;
use App\Helpers\ResponseCode;
use App\Helpers\ResponseFormatter;
use App\Helpers\Status;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\CartDetailRequest;
use App\NumberDelete;
use App\NumberStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller {
	protected $guard = 'user';
	/**
	 * @var int
	 */
	private $userId;
	/**
	 * @var CartHeader
	 */
	private $cartHeader;
	/**
	 * @var CartDetail
	 */
	private $cartDetail;
	private $cartHeaderId;
	/**
	 * @var ResponseFormatter
	 */
	private $responseFormatter;
	/**
	 * @var Calculator
	 */
	private $calculator;
    /**
     * @var NumberDelete
     */
    private $numberDelete;
	/**
	 * @var NumberStore
	 */
	private $numberStore;

	public function __construct(CartHeader $cartHeader, CartDetail $cartDetail, ResponseFormatter $responseFormatter,Calculator $calculator, NumberDelete $numberDelete, NumberStore $numberStore) {
		$this->middleware('auth:' . $this->guard);
		if ($user = auth($this->guard)->user()) {
			$this->userId = auth($this->guard)->user()->id;
			$this->cartHeader = $cartHeader::whereUserId($this->userId)->first();

			if ($this->cartHeader) {
				$this->cartHeaderId = $this->cartHeader->id;
			} else {
				$this->cartHeader = $cartHeader::create(['user_id' => $this->userId, "limit" => 50]);
				$this->cartHeaderId = $this->cartHeader->id;
			}
		}



		$this->cartDetail = $cartDetail;

		$this->responseFormatter = $responseFormatter;
		$this->calculator = $calculator;
        $this->numberDelete = $numberDelete;
		$this->numberStore = $numberStore;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(IndexRequest $request) {
		//
		$data = $request->all();

		$response = $this->cartDetail
			->withRelatedModels()
			->whereCartHeaderId($this->cartHeaderId);

		if (isset($data['order_by']) && $data['order_by']) {
			$response = $response->orderBy($data['order_by_key'], $data['order_by']);
		}
		$response = $response->whereHas('itemStock',function ($query){
			$query->whereHas('sizeOption', function ($query){
				$query->whereStatusOptionId(Status::ACTIVE);
			});
			$query->whereHas('itemVariant', function ($query){
				$query->whereHas('colorOption',function ($query){
					$query->whereStatusOptionId(Status::ACTIVE);
				});

			});
		});

		$response = json_decode($data['paginate']) ? $response->paginate($data['per_page'])->toArray() : $response->get()->toArray();


		if (!json_decode($data['paginate'])) {
			$arrays = $response;
			$response = [];
			foreach ($arrays as $key => $arr) {
				array_push($response, $this->responseFormatter->toCart($arr));
			}

			return $this->responseSuccess($response, ResponseCode::OKAY);
		} else {
			$arrays = $response['data'];
			$response['data'] = [];
			foreach ($arrays as $key => $arr) {
				array_push($response['data'], $this->responseFormatter->toCart($arr));
			}

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
	public function store(CartDetailRequest $request) {
		//
		$data = $request->all();
		$data['cart_header_id'] = $this->cartHeaderId;
		if ($response = $this->cartDetail->whereCartHeaderId($data['cart_header_id'])->whereItemStockId($data['item_stock_id'])->first()) {
			$inputs = [
				'quantity' => $data['quantity'] + $response->quantity,
			];
			$response->fill($inputs)->save();

		} else {
			$response = $this->cartDetail->create($data);

		}

		$this->numberStore->storeAsCartItem($data['item_stock_id']);

		$response = json_decode($this->show($response->id)->content(), true);

		return $this->responseSuccess($response['data'], ResponseCode::OKAY);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//

		if($response = $this->cartDetail
			->withRelatedModels()->find($id)){
			$response = $this->responseFormatter->toCart($response->toArray());

			return $this->responseSuccess($response, ResponseCode::OKAY);
		}

		return $this->responseFailWithCode(ResponseCode::NOT_FOUND);
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
	public function update(CartDetailRequest $request, $id) {
		//

		$data = $request->all();

		if ($response = $this->cartDetail->getModel()->find($id)) {
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

		if ($response = $this->cartDetail->getModel()->find($id)) {

			$res = json_decode($this->show($id)->content(), true);

			$this->numberDelete->storeAsCartItem($response->item_stock_id);
			$response->delete();

			$total = json_decode($this->getItemCountAndTotal()->content(), true);

			$inputs = [
				"item" => $res['data'],
				"total" => $total['data']
			];
			return $this->responseSuccess($inputs, 200);
		}


		return $this->responseFailWithCode(ResponseCode::NOT_FOUND);


	}

	public function getItemCountAndTotal() {

		$cart = $this->cartDetail
			->withRelatedModels()
			->whereCartHeaderId($this->cartHeaderId)->whereHas('itemStock',function ($query){
			$query->whereHas('sizeOption', function ($query){
				$query->whereStatusOptionId(Status::ACTIVE);
			});
			$query->whereHas('itemVariant', function ($query){
				$query->whereHas('colorOption',function ($query){
					$query->whereStatusOptionId(Status::ACTIVE);
				});

			});
		})->get();
		$cart = $cart->map(function ($response){
			$items  = $response;
			$items->price = $items->itemStock->price;

			return $items;
		});
		$total = $this->calculator->calculateTotal($cart);
		$shippingFee = $this->calculator->calculateShippingRate($total);
		$grandTotal = $this->calculator->calculateGrandTotal($total,0,$shippingFee);

		$response = [
			"total_items" => $cart->sum('quantity'),
			"sub_total"    => $total,
			"discount"     => 0,
			"grand_total"  => $grandTotal,
			"shipping_fee" => $shippingFee,
		];

		return $this->responseSuccess($response, ResponseCode::OKAY);
	}
}
