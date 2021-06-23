<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;


class EventRequest extends FormRequest
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
        $regex = '/^(https?:\/\/)|([www\.-]{3}+)\.([a-z\.]{2,6})([\/\w \.-]*)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        return [
            'title'         => "required",
            'startDate'    => "required|after:yesterday",
            'endDate'      => "required|after_or_equal:startDate",
            'startTime'    => "required",
            'endTime'      => "required",
            'location'      => "required",
            'city'        => "required",
            'country'       => "required",
            'description'   => "required",
            'link'          => ["required", "regex:".$regex],
            'admissionFee' => "required",
            'organizer.name' => "required",
            'organizer.imageUrl' => "required",
            'eventDays.*.schedules.*.title' => ['required'],
            'segments.*.title' => ['required'],
            'segments.*.segmentExhibitors.*.description' => ['required'],
            'eventImages' => 'required|array|max:5'
        ];



    }

    public function messages()
    {
        return  [
            'title.required' => 'The event title field is required.',//
            'description.required' => "The event description field is required.",//
            'location.required' => "The event location field is required.",//
            'country.required' => "The event country field is required.",//
            'admissionFee.required' => "The event admission fee field is required.",//
            'link.required' => "The event link field is required.",//
            'link.regex' => "The event link is not valid url.",//
            'organizer.name.required' => 'The event organizer field is required.',//
            'organizer.imageUrl.required' => 'The event organizer image is required.',//
            'eventDays.*.schedules.*.title' => 'The title field is required.',//
            'segments.*.title' => 'The segment title field is required.',//
            'segments.*.segmentExhibitors.*.description' => 'The list field is required',
            'segments.*.locationImages.*.path' => 'The image field is required',//
            'segments.*.locationImages.*.fileName' => 'The image field is required',//
            'eventImages' => 'The image field is required'
        ];
    }

    public function attributes()
    {
        return [
            'eventDays.*.schedules.*.title' => 'title',
            'segments.*.title' => 'segment section',
            'segments.*.segmentExhibitors.*.description' => 'list',
            'segments.*.locationImages.*.path' => 'image',//
            'segments.*.locationImages.*.fileName' => 'image',//
        ];
    }
}
