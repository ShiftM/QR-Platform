<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\App;

class CountTotalRow implements Rule {


	private $model;
	private $max;

	public function __construct($model, $max) {
		$this->model = App::make($model);
		$this->max  = $max;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {



		return $this->model->count() < $this->max;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
		return 'Unable store more record. Please remove some item.';
	}
}
