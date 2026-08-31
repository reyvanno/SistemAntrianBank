<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Counter;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'exists:roles,name',
            ],

            'counter_id' => [
                'nullable',
                'integer',
                'exists:counters,id',
                function ($attribute, $value, $fail) {
                    $this->validateCounter(
                        $value,
                        $fail
                    );
                },
            ],

            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique(
                    'users',
                    'username'
                )->ignore($this->user),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($this->user),
            ],

            'password' => [
                'nullable',
                'min:8',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    protected function validateCounter(
        $counterId,
        $fail
    ): void {
        $role = $this->input('role');

        if (!$counterId) {
            if (
                in_array(
                    $role,
                    ['teller', 'customer_service']
                )
            ) {
                $fail(
                    'Loket wajib dipilih untuk role ini.'
                );
            }

            return;
        }

        $counter = Counter::with('service')
            ->find($counterId);

        if (!$counter) {
            return;
        }

        if (
            $role === 'teller'
            && $counter->service?->code !== 'A'
        ) {
            $fail(
                'Teller hanya dapat ditempatkan pada loket Teller.'
            );
        }

        if (
            $role === 'customer_service'
            && $counter->service?->code !== 'B'
        ) {
            $fail(
                'Customer Service hanya dapat ditempatkan pada loket Customer Service.'
            );
        }

        if (
            !in_array(
                $role,
                ['teller', 'customer_service']
            )
        ) {
            $fail(
                'Role ini tidak dapat ditempatkan pada loket.'
            );
        }
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',

            'counter_id.exists' => 'Loket yang dipilih tidak valid.',
            'counter_id.integer' => 'Loket tidak valid.',

            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.alpha_dash' => 'Username hanya boleh menggunakan huruf, angka, tanda hubung, dan underscore.',
            'username.unique' => 'Username sudah digunakan.',

            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',

            'password.min' => 'Password minimal 8 karakter.',

            'is_active.required' => 'Status wajib dipilih.',
        ];
    }
}