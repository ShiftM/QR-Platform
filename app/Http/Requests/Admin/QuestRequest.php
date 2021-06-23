<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuestRequest extends FormRequest
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
            'eventId'    => "required",
            'boothId'    => "required",
            'title' => 'required', 
            'description' => 'required', 
            'points' => 'required|regex:/^[0-9]*$/'
        ];
    }

    public function attributes()
    {
        return [
            'eventId'    => 'event',
            'title' => 'Title', 
            'description' => 'Description', 
            'points' => 'Points'
        ];
    }
}
