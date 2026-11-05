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
        $user = $this->user();
        $userKey = $user->run ?? $user->getKey();

        $rules = [
            'name' => ['nullable', 'string', 'max:255'],
            'celular' => ['nullable', 'string', 'regex:/^9\d{8}$/'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'anio_ingreso' => ['nullable', 'integer', 'min:2010', 'max:' . date('Y')],
        ];

        // Normalizar emails para comparar sin distinción de mayúsculas ni espacios
        $submittedEmail = strtolower(trim((string)$this->input('email')));
        $currentEmail = strtolower(trim((string)($user->email ?? '')));

        // Solo aplica unique si el email ingresado es efectivamente diferente al actual del usuario
        if ($this->filled('email') && $submittedEmail !== $currentEmail) {
            $rules['email'] = [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userKey, 'run'),
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

    /**
     * Mensajes de validación personalizados
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'El correo electrónico ya está registrado por otro usuario.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'celular.regex' => 'El número de celular debe tener el formato 9XXXXXXXX (9 dígitos comenzando con 9).',
        ];
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
