<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class XeroTimesheetPreviewRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}
