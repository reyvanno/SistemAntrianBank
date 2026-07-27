<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => [
                'required',
                'exists:services,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'service_id.required' => 'Silakan pilih layanan terlebih dahulu.',
            'service_id.exists' => 'Layanan yang dipilih tidak valid.',

        ];
    }
}