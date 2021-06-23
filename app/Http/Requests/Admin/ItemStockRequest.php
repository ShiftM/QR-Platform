<?php

namespace App\Http\Requests\Admin;

use App\Helpers\ResponseFormatter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use ResponseCode;

class ItemStockRequest extends FormRequest
{
    public function __construct(){

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
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $rules = [
            "POST" => [
                "item_id"=> "required",
                "currency_type_id"=> "required",
                "status_option_id"=> "required",
                "quantity"=> "required",
                "price"=> "required",
                "sku"=> "required|unique:item_stocks",
            ],
            "PUT"  => [
                "item_id"=> "required",
                "currency_type_id"=> "required",
                "status_option_id"=> "required",
                "quantity"=> "required",
                "price"=> "required",
                "sku"=> "required",
            ]
        ];


        return $rules[ $this->getMethod() ];
    }
}
