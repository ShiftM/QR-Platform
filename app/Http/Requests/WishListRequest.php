<?php

namespace App\Http\Requests;

use App\Helpers\ResponseFormatter;
use App\Rules\ValidateItemStock;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use ResponseCode;

class WishListRequest extends FormRequest
{

    public function __construct()
    {

        $this->responseFormatter = new ResponseFormatter();

    }

    protected function failedValidation(Validator $validator)
    {


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
        return [
            "item_stock_id" => ["required","integer",new ValidateItemStock('App\\ItemStock', 'id')],
        ];
    }
}
