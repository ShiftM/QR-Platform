<?php

namespace App\Http\Requests;

use App\Helpers\ResponseFormatter;
use App\Rules\ValidateOrder;
use App\Rules\ValidateOrderBalance;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use ResponseCode;

class OrderRequest extends FormRequest {
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
				"order_id"     => [
					new ValidateOrder('App\\OrderHeaderTemp', 'id'),
				],
				"order_number" => [
					new ValidateOrder('App\\OrderHeaderTemp', 'order_number'),
					new ValidateOrderBalance('App\\OrderHeaderTemp', 'order_number'),
				],
				"order_shipping.house_number"=>"required",
				"order_shipping.street"=>"required",
				"order_shipping.zip_code"=>"required",
				"order_shipping.city"=>"required",
				"order_shipping.country"=>"required",

				"order_recipient.phone_number"=>"required",
				"order_recipient.full_name"=>"required",
			],
			"PUT"  => [

			],
		];

		return $rules[ $this->getMethod() ];
	}
}
