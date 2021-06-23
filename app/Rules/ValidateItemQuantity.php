<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\App;

class ValidateItemQuantity implements Rule {


	private $model;
	private $method;
	private $oldQuantity;

	public function __construct($model, $itemStockId, $method,$detailId) {
		$this->model = App::make($model)->find($itemStockId);
		$this->method = $method;
		if($detailId){
			$this->oldQuantity = App::make('App\CartDetail')->find($detailId)->quantity;
		}

	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {

		if(!$this->model){
			return false;
		}
		if ($this->method == "POST") {
			return $this->model->remaining_quantity >= $value;
		}

		if ($this->method == "PUT") {
			$qty = 0;
			$stock = $this->model->remaining_quantity + $this->oldQuantity;


			return $stock >= $value;
		}

	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
		if ($this->method == "POST") {	
			return 'Out of Stock.';
		}

		if ($this->method == "PUT") {
			return 'Out of Stock.';

		}

	
	}
}
