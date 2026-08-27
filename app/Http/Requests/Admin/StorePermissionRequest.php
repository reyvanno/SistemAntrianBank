<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', 'web'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'Nama permission wajib diisi.',

            'name.string' =>
                'Nama permission harus berupa teks.',

            'name.max' =>
                'Nama permission maksimal 255 karakter.',

            'name.unique' =>
                'Permission tersebut sudah digunakan.',
        ];
    }
}