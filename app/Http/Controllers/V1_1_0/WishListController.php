<?php

namespace App\Http\Controllers\V1_1_0;

use App\Helpers\ResponseCode;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\WishListRequest;
use App\Repositories\Rest\RestRepository;
use App\WishListDetail;
use App\WishListHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WishListController extends Controller {
	protected $guard = 'user';
	/**
	 * @var RestRepository
	 */
	private $rest;
	private $detail;
	private $userId;
	/**
	 * @var ResponseFormatter
	 */
	private $responseFormatter;

	public function __construct(WishListHeader $rest, WishListDetail $detail, ResponseFormatter $responseFormatter) {
		$this->middleware('auth:' . $this->guard);
		$this->rest = new RestRepository($rest);
		$this->detail = new RestRepository($detail);
		$this->userId = auth($this->guard)->user()->id;

		$this->responseFormatter = $responseFormatter;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(IndexRequest $request) {
		$data = $request->all();

		$data['user_id'] = $this->userId;
		$response = $this->detail->with(
			[
				'wishListHeader',
				'itemStock' => function ($query) {
					$query->withRelatedModels();
				},
			])
			->whereHas('wishListHeader', function ($query) use ($data) {
				$query->whereUserId($data['user_id']);
			});

		$response = json_decode($data['paginate']) ? $response->paginate($data['per_page'])->toArray() : $response->get()->toArray();
		if (!json_decode($data['paginate'])) {
			$arrays = $response;
			$response = [];
			foreach ($arrays as $key => $arr) {
				array_push($response, $this->responseFormatter->toWishList($arr));
			}

			return $this->responseSuccess($response, 200);
		} else {
			$arrays = $response['data'];
			$response['data'] = [];
			foreach ($arrays as $key => $arr) {
				array_push($response['data'], $this->responseFormatter->toWishList($arr));
			}

			return $this->responseSuccessWithPagination($response, 200);
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
	public function store(WishListRequest $request) {
		$data = $request->all();
		$data['user_id'] = $this->userId;

		$response = $this->rest->getModel()->whereUserId($data['user_id'])->first();
		if (!$response) {
			$response = $this->rest->create($data);
		}
		$data['wish_list_header_id'] = $response->id;

		$checkItem = $this->detail->getModel()
			->where('item_stock_id', $data['item_stock_id'])
			->where('wish_list_header_id', $data['wish_list_header_id'])->first();

		if ($checkItem) {
			$id = $checkItem->id;
		} else {
			$s = $this->detail->create($data);
			$id = $s->id;
		}


		$response = json_decode($this->show($id)->content(), true);

		return $this->responseSuccess($response['data'], 200);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$response = $this->detail->with(
			[
				'wishListHeader',
				'itemStock' => function ($query) {
					$query->withRelatedModels();
				},
			])->find($id)->toArray();

		$response = $this->responseFormatter->toWishList($response);

		return $this->responseSuccess($response, 200);

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
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {

		$userId = $this->userId;
		$response = $this->detail->getModel()
			->whereHas('wishListHeader', function ($query) use ($userId) {
				$query->whereUserId($userId);
			})
			->whereItemStockId($id)->first();
		if ($response) {

			$res = json_decode($this->show($response->id)->content(), true);

			$response->delete();

			return $this->responseSuccess($res['data'], 200);
		}


		return $this->responseFailWithCode(ResponseCode::NOT_FOUND);


	}
}
