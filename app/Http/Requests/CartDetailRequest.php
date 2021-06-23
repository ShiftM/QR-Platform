<?php

namespace App\Http\Requests;

use App\Helpers\ResponseFormatter;
use App\Rules\ValidateItemExist;
use App\Rules\ValidateItemQuantity;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use ResponseCode;

class CartDetailRequest extends FormRequest {
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
				"item_stock_id" => ['required',
					new  ValidateItemExist(),
				],
				"quantity"      => [
					'required',
					'numeric',
					'min:0',
					'not_in:0',
					new ValidateItemQuantity(
						'App\\ItemStock',
						$this->request->get('item_stock_id'),
						$this->getMethod(),
						null),
				],
			],
			"PUT"  => [
				"item_stock_id" => ['required'],
				"id"            => ['required'],
				"quantity"      => [
					'required',
					'numeric',
					'min:0',
					'not_in:0',
					new ValidateItemQuantity(
						'App\\ItemStock',
						$this->request->get('item_stock_id'),
						$this->getMethod(),
						$this->request->get('id')),
				],
			],
		];

		return $rules[ $this->getMethod() ];
	}
}
