<?php

namespace App\Http\Controllers\V1_1_0;

use App\Helpers\ResponseFormatter;
use App\Helpers\Status;
use App\Http\Requests\Admin\IndexRequest;
use App\Item;
use App\ItemStock;
use App\NumberView;
use App\OrderHeader;
use App\OrderHeaderTemp;
use App\WishListDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use function GuzzleHttp\Promise\all;

class ItemController extends Controller {

	protected $guard = 'user';
	/**
	 * @var OrderHeaderTemp
	 */
	private $orderHeaderTemp;
	/**
	 * @var OrderHeader
	 */
	private $orderHeader;
	/**
	 * @var int
	 */
	private $userId;
	/**
	 * @var Item
	 */
	private $item;
	/**
	 * @var ResponseFormatter
	 */
	private $responseFormatter;
	/**
	 * @var WishListDetail
	 */
	private $wishListDetail;
	/**
	 * @var ItemStock
	 */
	private $itemStock;
	/**
	 * @var NumberView
	 */
	private $numberView;

	public function __construct(Item $item, ResponseFormatter $responseFormatter, WishListDetail $wishListDetail, ItemStock $itemStock, NumberView $numberView) {

		$this->middleware('auth:' . $this->guard);
		$this->item = $item;
		$this->responseFormatter = $responseFormatter;
		$this->wishListDetail = $wishListDetail;
		$this->itemStock = $itemStock;
		$this->numberView = $numberView;
	}


	public function isInWishList($itemId) {

		$response = false;
		if ($user = auth($this->guard)->user()) {
			$data = $this->wishListDetail->with([
				'wishListHeader', 'itemStock',
			])
				->whereHas('wishListHeader', function ($query) use ($user) {
					$query->whereUserId($user->id);
				})
				->whereHas('itemStock', function ($query) use ($itemId) {
					$query->whereItemId($itemId);
				})
				->first();
			if ($data) {
				$response = true;
			}
		}

		return $response;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getStockIds() {

		$detail = $this->itemStock->withRelatedModels()
			->whereHas('itemVariant', function ($query) {
				$query->wherePrimary(1);
			})
			->groupBy('item_id')->pluck('id');

		return $detail;

	}

	public function index(IndexRequest $request) {
		//

		$data = $request->all();
		$response = $this->item;

		if (isset($data['with_related_models']) && json_decode($data['with_related_models'])) {
			$response = $response->withRelatedModels($data);
		}


		if (isset($data['name']) && $data['name']) {
			$response = $response->where('name', 'like', '%' . $data['name'] . '%');
		}


		if (isset($data['status_option_id']) && $data['status_option_id']) {
			$response = $response->whereStatusOptionId($data['status_option_id']);
		}


		if (isset($data['is_featured']) && json_decode($data['is_featured'])) {
			$response = $response->whereHas('hasOneFeaturedItem');
		}

		if (isset($data['category_header_id']) && $data['category_header_id']) {
			$response = $response->whereHas('hasManyItemCategory', function ($query) use ($data) {
				$query->whereCategoryHeaderId($data['category_header_id']);
			});
		}


		if (isset($data['order_by']) && $data['order_by']) {
			$response = $response->orderBy($data['order_by_key'], $data['order_by']);
		}


		$response = json_decode($data['paginate']) ? $response->paginate($data['per_page'])->toArray() : $response->get()->toArray();

		if (!json_decode($data['paginate'])) {
			$arrays = $response;
			$response = [];

			foreach ($arrays as $key => $arr) {
				array_push($response, $this->responseFormatter->toItem($arr));
			}

			return $this->responseSuccess($response, 200);
		} else {
			$arrays = $response['data'];
			$response['data'] = [];

			foreach ($arrays as $key => $arr) {
				$valid = false;
				if(count($arr['has_many_item_variant']) >= 1){
					foreach ($arr['has_many_item_variant'] as $ks => $s){
						if(count($s['has_many_item_stock']) >= 1){
							$valid =  true;
						}
					}

				}

				if($valid){
					$arr['is_in_wish_list'] = $this->isInWishList($arr['id']);
					array_push($response['data'], $this->responseFormatter->toItem($arr));
				}

			}

			return $this->responseSuccessWithPagination($response, 200);
		}

	}

	public function stocks(Request $request) {

		$data = $request->all();
		$stocks = $this->getStockIds();
		$response = $this->itemStock;
		if (isset($data['with_related_models']) && json_decode($data['with_related_models'])) {
			$response = $response->withRelatedModels($data);
		}

		if (isset($stocks) && $stocks) {
			$response = $response->whereIn('id', $stocks);
		}

		$response = $response->whereHas('sizeOption', function ($query) use ($data) {
			$query->whereStatusOptionId(Status::ACTIVE);
		});
		$response = $response->whereHas('itemVariant', function ($query) use ($data) {

			$query->whereHas('colorOption', function ($query) {
				$query->whereStatusOptionId(Status::ACTIVE);
			})->wherePrimary(1);
		});
		$response = $response->whereHas('item', function ($query) use ($data) {


			if (isset($data['is_featured']) && json_decode($data['is_featured'])) {
				$query->whereHas('hasOneFeaturedItem');
			}


			if (isset($data['status_option_id']) && $data['status_option_id']) {
				$query->whereStatusOptionId($data['status_option_id']);
			}

			if (isset($data['name']) && $data['name']) {
				$query->where('name', 'like', '%' . $data['name'] . '%');
			}

			if (isset($data['category_header_id']) && $data['category_header_id']) {
				$query->whereHas('hasManyItemCategory', function ($query) use ($data) {
					$query->whereCategoryHeaderId($data['category_header_id']);
				});
			}

		});


		$response = json_decode($data['paginate']) ? $response->paginate($data['per_page'])->toArray() : $response->get()->toArray();

		if (!json_decode($data['paginate'])) {
			$arrays = $response;
			$response = [];
			foreach ($arrays as $key => $arr) {
				array_push($response, $this->responseFormatter->toStock($arr));
			}

			return $this->responseSuccess($response, 200);
		} else {
			$arrays = $response['data'];
			$response['data'] = [];
			foreach ($arrays as $key => $arr) {
				array_push($response['data'], $this->responseFormatter->toStock($arr));
			}

			return $this->responseSuccessWithPagination($response, 200);
		}

	}

	public function suggestions(Request $request) {
		$data = $request->all();
		$response = $this->item;

		if (isset($data['name']) && $data['name']) {
			$response = $response->where('name', 'like', '%' . $data['name'] . '%');
		}

		if (isset($data['status_option_id']) && $data['status_option_id']) {
			$response = $response->whereStatusOptionId($data['status_option_id']);
		}


		$response = $response->groupBy('name')->get('name');

		if (isset($data['limit']) && $data['limit']) {
			$response = $response->take($data['limit']);
		}

		return $this->responseSuccess($response, 200);
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

	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id, Request $request) {
		$data = $request->all();

		$response = $this->item;
		if (isset($data['with_related_models']) && json_decode($data['with_related_models'])) {
			$response = $response->withRelatedModels($data);
		}


		$response = $response->find($id)->toArray();
		$response['is_in_wish_list'] = $this->isInWishList($response['id']);
		$response = $this->responseFormatter->toItem($response);
		$this->numberView->storeAsItem($id);

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
		//
	}
}
