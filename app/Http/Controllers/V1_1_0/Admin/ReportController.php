<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Item;
use App\ItemStock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\Promise\all;

class ReportController extends Controller {

	/**
	 * @var ItemStock
	 */
	private $itemStock;
	/**
	 * @var Item
	 */
	private $item;

	public function __construct(ItemStock $itemStock, Item $item) {

		$this->itemStock = $itemStock;
		$this->item = $item;
	}

	public function mostSoldByPrice(Request $request) {

		$data = $request->all();


		$item = $this->item
			->with([
				'hasManyItemCategory'=> function ($query){
					$query->with(['categoryHeader']);
				},
				'hasManyItemStock' => function ($query) {
				$query->with([
					'sizeOption', 'itemVariant' => function ($query) {
						$query->with(['colorOption']);
					},
				])
					->withCount([
						'hasManyOrderDetail as total_sold_price' => function ($query) {
							$query->select(DB::raw("SUM(sub_total)"));
						},
					])
					->having('total_sold_price', '>', 0)
					->orderBy('total_sold_price', 'DESC');
			}])
			->get();
		$item = $item->map(function ($item) {
			$total = 0;


			foreach ($item->hasManyItemStock as $key => $i) {
				$total = $i->total_sold_price + $total;
			}
			$item->total_price = $total;

			return $item;
		})->toArray();

		usort($item, function ($a, $b) {
			return $b['total_price'] - $a['total_price'];
		});

		$collection = collect($item)->where('total_price', '>', 0)->take(10)->all();;

		return listResponse($collection);

	}

	public function mostSoldByQuantity(Request $request) {

		$data = $request->all();


		$item = $this->item
			->with([
				'hasManyItemCategory'=> function ($query){
				$query->with(['categoryHeader']);
				},
				'hasManyItemStock' => function ($query) {
				$query->with([
					'sizeOption', 'itemVariant' => function ($query) {
						$query->with(['colorOption']);
					},
				])
					->withCount([
						'hasManyOrderDetail as total_quantity_sold' => function ($query) {
							$query->select(DB::raw("SUM(quantity)"));
						},
					])
					->having('total_quantity_sold', '>', 0)
					->orderBy('total_quantity_sold', 'DESC');
			}])
			->get();
		$item = $item->map(function ($item) {
			$total = 0;


			foreach ($item->hasManyItemStock as $key => $i) {
				$total = $i->total_quantity_sold + $total;
			}
			$item->total_quantity = $total;

			return $item;
		})->toArray();

		usort($item, function ($a, $b) {
			return $b['total_quantity'] - $a['total_quantity'];
		});

		$collection = collect($item)->where('total_quantity', '>', 0)->take(10)->all();;

		return listResponse($collection);

	}
}
