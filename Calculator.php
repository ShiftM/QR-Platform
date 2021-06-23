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
	public function calculateCartItemsTotal($cart) {

		$subtotal = 0;

		foreach ($cart as $c) {

			$itemSubtotal = $c['price'] * $c['qty'];

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
	public function calculateCartGrandTotal($subtotal, $discount, $type) {

		$grandTotal = $subtotal;

		//Fixed price
		if ($type == 1) {
			$grandTotal = $subtotal - $discount;
		} //Percentage
		else if ($type == 2) {

			$decimal = $discount / 100;

			$subtract = $subtotal * $decimal;

			$grandTotal = $subtotal - $subtract;

		}

		return $grandTotal;

	}

	/**
	 * Calculate cart grandtotal with store coupon and total it with other items
	 * @param $discount
	 * @param $type
	 * @param $storeId
	 * @param $cart
	 * @return mixed
	 */
	public function calculateCartGrandTotalWithStoreCoupon($discount, $type, $storeId, $cart) {

		$qualifiedItemsTotal = 0;
		$itemsWithoutDiscount = 0;


		foreach ($cart as $item) {

			if ($item['options']['storeId'] == $storeId) {

				$qualifiedItemsTotal += $item['options']['subtotal'];

			} else {

				$itemsWithoutDiscount += $item['options']['subtotal'];

			}
		}

		//Fixed price
		if ($type == 1) {

			$discountedItemsSubtotal = $qualifiedItemsTotal - $discount;

		} //Percentage
		else if ($type == 2) {
	
			$decimal = $discount / 100;

			$subtract = $qualifiedItemsTotal * $decimal;

			$discountedItemsSubtotal = $qualifiedItemsTotal - $subtract;

		}

		//If discounted items is below zero, make it 0 instead of a negative value
		if ($discountedItemsSubtotal < 0) {
			$discountedItemsSubtotal = 0;
		}

		$grandTotal = $discountedItemsSubtotal + $itemsWithoutDiscount;

		return $grandTotal;

	}


	/**
	 * Calculate shipping rate based on minimum subtotal
	 * @param $subtotal
	 * @return int
	 */
	public function calculateShippingRate($subtotal, $minimumSubtotal = 0) {

		$fee = 0;

		// $minimumSubtotal = 800;

		if ($subtotal < $minimumSubtotal) {
			$fee = $minimumSubtotal - $subtotal;
		}

		return $fee;


	}

	/**
	 * Calculate order grand total in checkout page
	 * @param $subtotal
	 * @param $shippingFee
	 * @param $discount
	 * @param $type discount int
	 * @return mixed
	 */
	public function calculateOrderGrandTotal($subtotal, $shippingFee, $discount, $type) {


		$grandTotal = ($subtotal + $shippingFee);

		//Fixed price
		if ($type == 1) {

			$grandTotal = ($subtotal + $shippingFee) - $discount;
		} //Percentage
		else if ($type == 2) {

			$decimal = $discount / 100;

			$subtract = $subtotal * $decimal;

			$grandTotal = $subtotal - $subtract;

			$grandTotal += $shippingFee;

		}

		return $grandTotal;

	}

}
