<?php

namespace App\Http\Requests\User;

use App\Helpers\ResponseFormatter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use ResponseCode;

class UserAddressRequest extends FormRequest
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
                "recipient_name"=> "required",
                "house_number"=> "required",
                "street"=> "required",
                "city"=> "required",
                "province"=> "required",
                "region"=> "required",
                "country"=> "required",
                "zip_code"=> "required",
                "phone_number"=> "required|size:10",
            ],
            "PUT"  => [
                "recipient_name"=> "required",
                "house_number"=> "required",
                "street"=> "required",
                "city"=> "required",
                "province"=> "required",
                "region"=> "required",
                "country"=> "required",
                "zip_code"=> "required",
                "phone_number"=> "required|size:10",
            ]
        ];


        return $rules[ $this->getMethod() ];
    }
    public function messages() {
        return [
            'phone_number.size' => 'Please make sure your phone number is correct.',
        ];
    }

}
