<?php

namespace App\Http\Requests;

use App\Models\IncidenciaEspacio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoEspacioRequest extends FormRequest
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
     *
     * Para pasar un espacio a Mantenimiento ahora es obligatorio indicar el motivo
     * (categoría + descripción), que queda registrado como incidencia.
     */
    public function rules(): array
    {
        $paraMantenimiento = $this->input('estado') === 'Mantenimiento';

        return [
            'estado' => 'required|in:Disponible,Ocupado,Mantenimiento',

            // Motivo del mantenimiento (obligatorio solo al pasar a Mantenimiento)
            'categoria' => [Rule::requiredIf($paraMantenimiento), 'nullable', Rule::in(array_keys(IncidenciaEspacio::CATEGORIAS))],
            'descripcion' => [Rule::requiredIf($paraMantenimiento), 'nullable', 'string', 'min:5', 'max:1000'],
            'severidad' => ['nullable', Rule::in(array_keys(IncidenciaEspacio::SEVERIDADES))],

            // Nota opcional al liberar (cómo se resolvió)
            'nota' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser uno de los siguientes: Disponible, Ocupado, Mantenimiento.',
            'categoria.required' => 'Debes indicar qué tipo de falla motiva el mantenimiento.',
            'categoria.in' => 'La categoría de la falla no es válida.',
            'descripcion.required' => 'Debes describir la falla que motiva el mantenimiento.',
            'descripcion.min' => 'La descripción de la falla debe tener al menos 5 caracteres.',
            'descripcion.max' => 'La descripción de la falla no puede superar 1000 caracteres.',
        ];
    }
}
