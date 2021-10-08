<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendToXeroRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required',
            'payroll_calendar' => 'required',
            'payroll_calendar_period' => 'required',
        ];
    }
}
