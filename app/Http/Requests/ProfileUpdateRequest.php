<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;
        $rules = [
            'name' => ['nullable', 'string', 'max:255'],
            'celular' => ['nullable', 'string', 'regex:/^9\\d{8}$/'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'anio_ingreso' => ['nullable', 'integer', 'min:2010', 'max:' . date('Y')],
        ];

        // Solo aplica unique si el email es diferente al actual
        if ($this->filled('email') && $this->input('email') !== $this->user()->email) {
            $rules['email'] = [
                'nullable',
                'string',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId),
            ];
        } else {
            $rules['email'] = [
                'nullable',
                'string',
                'email',
                'max:255',
            ];
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->input('email'))),
            ]);
        }
    }
}
