<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BoothRequest extends FormRequest
{
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
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            // 'accountType' => 'required',
            'areaCode' => 'sometimes|unique:booths',
            'username' => 'required|unique:booths'
        ];
    }

    public function messages()
    {
        return [
            'areaCode.unique' => 'The phone number already exist.',
        ];
    }
}
