<?php namespace App;

class Calculator {

	/**
	 * @param $qty
	 * @param $price
	 * @return mixed
	 */
	public static function calculateSubtotal($qty, $price) {

		$subtotal = $qty * $price;

		return $subtotal;

	}

	/**
	 * @param $cart
	 * @return int
	 */
	public function calculateTotal($cart) {

		$subtotal = 0;

		foreach ($cart as $c) {

			$itemSubtotal = $c['price'] * $c['quantity'];

			$subtotal += $itemSubtotal;

		}


		return $subtotal;
	}

	/**
	 * @param $subtotal
	 * @param $discount
	 * @param $type = discount type id
	 * @return mixed
	 */
	public function calculateGrandTotal($subtotal, $discount, $shippingFee) {


		return $subtotal + $shippingFee + $discount;

	}


	/**
	 * Calculate shipping rate based on minimum subtotal
	 * @param $subtotal
	 * @return int
	 */
	public function calculateShippingRate($subtotal, $minimumSubtotal = 0) {

		$fee = 0;

		$minimumSubtotal = 0;

		if ($subtotal < $minimumSubtotal) {
			$fee = $minimumSubtotal - $subtotal;
		}

		return $fee;


	}


}
