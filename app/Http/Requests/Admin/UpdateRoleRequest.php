<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($this->role->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')
                    ->where('guard_name', 'web'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama role wajib diisi.',

            'name.string' => 'Nama role harus berupa teks.',

            'name.max' => 'Nama role maksimal 255 karakter.',

            'name.unique' => 'Nama role sudah digunakan.',

            'description.string' =>
                'Deskripsi role harus berupa teks.',

            'description.max' =>
                'Deskripsi role maksimal 1000 karakter.',

            'permissions.array' =>
                'Format permission tidak valid.',

            'permissions.*.integer' =>
                'Permission yang dipilih tidak valid.',

            'permissions.*.exists' =>
                'Permission yang dipilih tidak tersedia.',
        ];
    }
}