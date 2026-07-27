<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:5',
                'unique:services,code',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'code.required' => 'Kode layanan wajib diisi.',
            'code.unique' => 'Kode layanan sudah digunakan.',
            'code.max' => 'Kode layanan maksimal 10 karakter.',

            'name.required' => 'Nama layanan wajib diisi.',
            'name.unique' => 'Nama layanan sudah digunakan.',
            'name.max' => 'Nama layanan maksimal 255 karakter.',

        ];
    }
}
