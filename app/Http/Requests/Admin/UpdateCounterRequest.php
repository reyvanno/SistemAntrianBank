<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCounterRequest extends FormRequest
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

            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('counters')->ignore($this->counter),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'service_id.required' => 'Layanan wajib dipilih.',
            'service_id.exists' => 'Layanan yang dipilih tidak valid.',

            'code.required' => 'Kode loket wajib diisi.',
            'code.unique' => 'Kode loket sudah digunakan.',
            'code.max' => 'Kode loket maksimal 10 karakter.',

            'name.required' => 'Nama loket wajib diisi.',
            'name.unique' => 'Nama loket sudah digunakan.',
            'name.max' => 'Nama loket maksimal 255 karakter.',

            'is_active.required' => 'Status wajib dipilih.',

        ];
    }
}
