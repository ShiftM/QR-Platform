<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\App;

class ValidateOrder implements Rule {


	private $model;
	private $field;


	public function __construct($model, $field) {
		$this->model = App::make($model);
		$this->field = $field;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {


		$order = $this->model->where($this->field,$value)->first();

		return $order;

//			return $this->model->quantity > $qty;

	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {

		return 'Invalid Order Details';

	}
}
