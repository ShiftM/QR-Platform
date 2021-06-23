<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\App;

/**
 * @property  field
 * @property  field
 */
class ValidateItemStock implements Rule {


	private $model;

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



		return $this->model->where($this->field,$value)->first();
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
		return 'Invalid Item.';
	}
}
