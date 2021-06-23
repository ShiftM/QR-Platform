<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\App;

class ValidateUserNumber implements Rule {


	private $model;
	private $field;
	private $countryCode;


	public function __construct($model,$countryCode) {
		$this->model = App::make($model);
		$this->countryCode = $countryCode;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {



		$response = $this->model
			->where('phoneNumber',$value)
			->where('countryCode',$this->countryCode)
			->first();

		return $response;

//			return $this->model->quantity > $qty;

	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {

		return 'User phone number does not exist.';

	}
}
