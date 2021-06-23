<?php


namespace App\Helpers;


class ResponseFormatter {


	public function toUnprocessableEntity($errors) {

		$response = [];
		foreach ($errors as $key => $err) {

			$inputs = [

				"status" => ResponseCode::UNPROCESSABLE_ENTITY,
				"detail" => $err[0],
				"field"  => $key,
			];
			array_push($response, $inputs);
		}

		return $response;
	}

	//item related
	public function toColorOption($data) {

		$inputs = [

			'id' => $data['id'],
			"name" => $data['name'],
			"slug" => $data['slug'],
			"hex"  => $data['hex'],
		];

		return $inputs;
	}

	public function toSizeOption($data) {

		$inputs = [

			'id' => $data['id'],
			"name" => $data['name'],
			"slug" => $data['slug'],
		];

		return $inputs;
	}

	public function toStock($data) {
		$inputs = [
			"id"                 => $data['id'],
			"price"              => $data['price'],
			"remaining_quantity" => $data['remaining_quantity'],
			"is_in_wish_list" => $data['is_in_wish_list'],
		];

		if (isset($data['size_option'])) {
			$inputs['size_option'] = $this->toSizeOption($data['size_option']);
		}
		if (isset($data['item'])) {
			$inputs['item'] = $this->toItem($data['item']);
		}

		if (isset($data['item_variant'])) {
			$inputs['item_variant'] = $this->toVariant($data['item_variant']);
		}

		return $inputs;
	}


	public function toImage($data) {
		$inputs = [
			"full_path" => $data['full_path'],
			"primary"   => isset($data['primary']) && $data['primary'] ? $data['primary'] : 0,
		];


		return $inputs;
	}

	public function toVariant($data) {

		$inputs = [

			'id' => $data['id'],
			"primary" => $data['primary'],
		];

		if (isset($data['color_option'])) {
			$inputs['color_option'] = $this->toColorOption($data['color_option']);
		}

		if (isset($data['has_many_item_stock'])) {
			$inputs['item_stocks'] = [];
			foreach ($data['has_many_item_stock'] as $key => $arr) {

				array_push($inputs['item_stocks'], $this->toStock($arr));
			}
		}

		if (isset($data['has_many_image'])) {
			$inputs['images'] = [];
			foreach ($data['has_many_image'] as $key => $arr) {
				array_push($inputs['images'], $this->toImage($arr));
			}
		}

		return $inputs;
	}

	public function toCategories($data) {
		$inputs = [

			'id' => $data['id'],
			'category_header_id' => $data['category_header_id'] ? $data['category_header_id'] : 0,
			"name" => $data['category_header']['name'],
			"slug" => $data['category_header']['slug'],
		];

		return $inputs;
	}

	public function toItem($data) {

		$inputs = [
			"id"          => $data['id'],
			"description" => $data['description'] ? $data['description'] : '',
			"name"        => $data['name'],
			"slug"        => $data['slug'],
			"is_featured" => $data['is_featured'],
			"is_in_wish_list" => isset($data['is_in_wish_list']) && $data['is_in_wish_list'] ? $data['is_in_wish_list'] : false,

		];
		if (isset($data['has_many_item_variant'])) {
			$inputs['item_variants'] = [];
			foreach ($data['has_many_item_variant'] as $key => $arr) {
				array_push($inputs['item_variants'], $this->toVariant($arr));
			}
		}

		if (isset($data['has_many_item_category'])) {
			$inputs['item_categories'] = [];
			foreach ($data['has_many_item_category'] as $key => $arr) {
			    if($arr['category_header']){
                    array_push($inputs['item_categories'], $this->toCategories($arr));
                }
			}
		}

		return $inputs;

	}

	public function toCart($data) {

		$inputs = [
			"id"            => $data['id'],
			"item_stock_id" => $data['item_stock_id'],
			"quantity"      => $data['quantity'],
		];

		if (isset($data['item_stock'])) {
			$inputs['item_stock'] = $this->toStock($data['item_stock']);

		}

		return $inputs;
	}


	//order related

	public function toOrderTotal($data) {
		$inputs = [

			'id' => $data['id'],
			"sub_total"    => $data['sub_total'],
			"discount"     => $data['discount'],
			"grand_total"  => $data['grand_total'],
			"shipping_fee" => $data['shipping_fee'],
		];

		return $inputs;
	}

	public function toOrderDetail($data) {
		$inputs = [

			'id' => $data['id'],
			"price"     => $data['price'],
			"quantity"  => $data['quantity'],
			"sub_total" => $data['sub_total'],
		];

		if (isset($data['item_stock'])) {
			$inputs['item_stock'] = $this->toStock($data['item_stock']);

		}


		return $inputs;

	}


	public function toOrderShipping($data) {
		$inputs = [
			'id' => $data['id'],
			"house_number"    => $data['house_number'],
			"street"          => $data['street'],
			"barangay"        => $data['barangay'],
			"zip_code"        => $data['zip_code'],
			"city"            => $data['city'],
			"province"        => $data['province'],
			"country"         => $data['country'],
			"region"          => $data['region'],
		];

		return $inputs;
	}


	public function toOrderRecipient($data) {
		$inputs = [
			'id' => $data['id'],
			"full_name"    => $data['full_name'],
			"phone_number" => $data['phone_number'],
			"tel_number"   => $data['tel_number'],
		];

		return $inputs;
	}

	public function toStatusOption($data) {
		$inputs = [
			'id' => $data['id'],
			"name"    => $data['name'],
		];

		return $inputs;
	}


	public function toOrder($data) {


		$inputs = [
			"id"           => $data['id'],
			"order_number" => $data['order_number'],
			"total_quantity" => intval(isset( $data['total_quantity']) &&  $data['total_quantity'] ?  $data['total_quantity'] :0),
		];
		if (isset($data['has_many_order_total'])) {
			$inputs['order_totals'] = [];
			foreach ($data['has_many_order_total'] as $key => $arr) {
				array_push($inputs['order_totals'], $this->toOrderTotal($arr));
			}
		}

		if (isset($data['has_many_order_detail'])) {
			$inputs['order_details'] = [];
			foreach ($data['has_many_order_detail'] as $key => $arr) {
				array_push($inputs['order_details'], $this->toOrderDetail($arr));
			}
		}
		if (isset($data['has_many_order_detail_temp'])) {
			$inputs['order_details'] = [];
			foreach ($data['has_many_order_detail_temp'] as $key => $arr) {
				array_push($inputs['order_details'], $this->toOrderDetail($arr));
			}
		}
		if (isset($data['has_one_order_shipping'])) {
			$inputs['has_one_order_shipping'] = $this->toOrderShipping($data['has_one_order_shipping']);
		}

		if (isset($data['has_one_order_recipient'])) {
			$inputs['order_recipient'] = $this->toOrderRecipient($data['has_one_order_recipient']);
		}

		if (isset($data['status_option'])) {
			$inputs['status_option'] = $this->toStatusOption($data['status_option']);
		}

		if (isset($data['has_many_order_status_history'])) {
			$inputs['order_status_histories'] = [];
			foreach ($data['has_many_order_status_history'] as $key => $arr) {
				array_push($inputs['order_status_histories'], $this->toStatusOption($arr['status_option']));
			}
		}

		return $inputs;
	}

	//toWishList

	public function toWishList($data){
		$inputs = [
			'id' => $data['id']
		];


		if (isset($data['item_stock'])) {
			$inputs['item_stock'] = $this->toStock($data['item_stock']);

		}

		return $inputs;
	}
}
