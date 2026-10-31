<?php

namespace App\Http\Requests;

use App\Models\IncidenciaEspacio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportarIncidenciaEspacioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_espacio' => 'required|string|max:50',
            'categoria' => ['required', Rule::in(array_keys(IncidenciaEspacio::CATEGORIAS))],
            'severidad' => ['nullable', Rule::in(array_keys(IncidenciaEspacio::SEVERIDADES))],
            'descripcion' => 'required|string|min:5|max:1000',
            // Si no se envía, se usa el valor por defecto de la categoría (p. ej. falta de plumones no bloquea la sala)
            'bloquea_uso' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'id_espacio.required' => 'Debes seleccionar el espacio afectado.',
            'categoria.required' => 'Debes seleccionar el tipo de falla.',
            'categoria.in' => 'El tipo de falla no es válido.',
            'severidad.in' => 'La severidad no es válida.',
            'descripcion.required' => 'Debes describir la falla.',
            'descripcion.min' => 'La descripción debe tener al menos 5 caracteres.',
            'descripcion.max' => 'La descripción no puede superar 1000 caracteres.',
        ];
    }
}
