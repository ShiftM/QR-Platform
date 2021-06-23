<?php

namespace App\Http\Requests;

use App\Helpers\ResponseFormatter;
use App\Rules\ValidateUserNumber;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use ResponseCode;

class GemCheckoutRequest extends FormRequest {
	public function __construct() {

		$this->responseFormatter = new ResponseFormatter();

	}

	protected function failedValidation(Validator $validator) {


		$response = clientErrorResponse($this->responseFormatter->toUnprocessableEntity($validator->errors()->getMessages()), ResponseCode::UNPROCESSABLE_ENTITY);

		throw new ValidationException($validator, $response);

	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {


		$rules = [
			"POST" => [
				"gem_package_id"     => ["required"],
				"email_address"     => ["required"],
				"phone_number" => [
					new ValidateUserNumber('App\\User',"+63"),
				]
			],
			"PUT"  => [

			],
		];

		return $rules[ $this->getMethod() ];
	}

	public function messages()
	{
		return  [
			'gem_package_id.required' => 'Please Select a Package.',//
		];
	}

}
