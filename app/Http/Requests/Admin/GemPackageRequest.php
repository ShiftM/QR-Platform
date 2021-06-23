<?php

namespace App\Http\Requests\Admin;

use App\Helpers\ResponseFormatter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use ResponseCode;

class GemPackageRequest extends FormRequest
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
                "name"=> "required|unique:gem_packages",
                "amount"=> "required|integer",
                "description"=> "required|max:255",
                "price"=> "required",
                "status_option_id"=> "required",
            ],
            "PUT"  => [
                "name"=> "required|unique:gem_packages,name,".request()->segment(count(request()->segments())),
                "amount"=> "required|integer",
                "description"=> "required|max:255",
                "price"=> "required",
                "status_option_id"=> "required",
            ]
        ];


        return $rules[ $this->getMethod() ];
    }
}
