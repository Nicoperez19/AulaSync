<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcesarEscaneoSalaRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'id_espacio' => 'nullable|string|max:50',
            'run' => 'required|string|max:100',
            'nombre' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'tipo_solicitante' => 'nullable|string|max:50',
            'paso' => 'nullable|string|max:50',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'run.required' => 'El RUN es obligatorio para procesar el escaneo.',
            'correo.email' => 'El formato del correo electrónico no es válido.',
        ];
    }
}
