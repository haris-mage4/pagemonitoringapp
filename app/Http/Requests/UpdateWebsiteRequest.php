<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWebsiteRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'base_url' => ['sometimes', 'required', 'url', 'max:2048'],
            'environment' => ['sometimes', 'required', 'string', 'in:production,staging'],
            'schedule' => ['sometimes', 'required', 'string', 'in:hourly,every_6_hours,daily,weekly'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
