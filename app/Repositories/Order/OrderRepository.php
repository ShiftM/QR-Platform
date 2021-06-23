<?php namespace App\Repositories\Order;

use App\Repositories\Order\Contracts\OrderInterface;
use Illuminate\Support\Facades\App;

class OrderRepository implements OrderInterface {

	private $cartHeader;
	private $calculator;
	private $itemStock;

	public function __construct() {


		$this->cartHeader = App::make('App\CartHeader');
		$this->calculator = App::make('App\Calculator');
		$this->itemStock = App::make('App\ItemStock');
	}

	public function content($id) {

		$cart = $this->cartHeader->with([
			'hasManyCartDetail' => function ($query) {
				$query->withRelatedModels()
					->orderBy('id', 'DESC');
			},
		])->whereUserId($id)->first();

		$inputs = [
			"user_id" => $cart->user_id,
			"details" => [],
			"totals"  => [],
		];

		foreach ($cart->hasManyCartDetail as $key => $c) {


			$input = [
				'currency_type_id' => $c->itemStock ? $c->itemStock->currency_type_id : 0,
				'color_option_id'  => $c->itemStock && $c->itemStock->itemVariant ? $c->itemStock->itemVariant->color_option_id : 0,
				'item_stock_id'    => $c->item_stock_id,
				'size_option_id'   => $c->itemStock ? $c->itemStock->size_option_id : 0,
				'price'            => $c->itemStock ? $c->itemStock->price : 0,
				'quantity'         => $c->quantity,
				'sub_total'        => $this->calculator->calculateSubtotal($c->quantity, $c->itemStock ? $c->itemStock->price : 0),
			];
			array_push($inputs['details'], $input);

		}

		$subtotal = $this->calculator->calculateTotal($inputs['details']);
		$shipping = $this->calculator->calculateShippingRate($subtotal);
		$discount = 0;
		$grandTotal = $this->calculator->calculateGrandTotal($subtotal, $discount, $shipping);
		$inputs["totals"][0] = [
			"currency_type_id" => 1,
			"sub_total"        => $subtotal,
			"shipping_fee"     => $shipping,
			"discount"         => $discount,
			"grand_total"      => $grandTotal,
		];

		return $inputs;
	}

	public function updateInventory($data, $method) {
		foreach ($data as $key => $d) {
			$this->adjustQuantity($d['item_stock_id'], $d['quantity'], $method);
		}
	}

	public function adjustQuantity($itemStockId, $quantity, $method) {
		//remove, add
		$stock = $this->itemStock->find($itemStockId);
		$new_quantity = 0;
		if ($method == 'remove') {
			$new_quantity = $stock->quantity - $quantity;
		}

		if ($method == 'add') {
			$new_quantity = $stock->quantity + $quantity;
		}

		$stock->quantity = $new_quantity;
		$stock->save();
	}
}
