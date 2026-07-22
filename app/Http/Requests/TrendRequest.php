<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TrendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'range' => ['required', 'string', 'in:24h,7d,30d,custom'],
            'from' => ['required_if:range,custom', 'date'],
            'to' => ['required_if:range,custom', 'date', 'after_or_equal:from'],
        ];
    }
}
