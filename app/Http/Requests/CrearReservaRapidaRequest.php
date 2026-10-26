<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CrearReservaRapidaRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'run' => 'required|string|max:20',
            'correo' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'tipo' => 'required|in:profesor,solicitante,colaborador',
            'id_asignatura' => 'nullable|string',
            'espacio' => 'required|string',
            'fecha' => 'required|date',
            'tipo_frecuencia' => 'nullable|in:puntual,recurrente',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha',
            'modulo_inicial' => 'required|integer|min:1|max:16',
            'modulo_final' => 'required|integer|min:1|max:16|gte:modulo_inicial',
            'observaciones' => 'nullable|string|max:500',
            'nombre_actividad' => 'nullable|string|max:255',
            'descripcion_actividad' => 'nullable|string|max:500',
            'forzar' => 'nullable|boolean',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('tipo') === 'colaborador' && !$this->filled('id_asignatura')) {
                $validator->errors()->add('id_asignatura', 'Debe seleccionar una asignatura para las reservas de profesor colaborador.');
            }

            if ($this->input('tipo') === 'solicitante' && !$this->filled('nombre_actividad')) {
                $validator->errors()->add('nombre_actividad', 'Debe indicar el nombre de la actividad para reservas de solicitantes externos.');
            }
        });
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'modulo_final.gte' => 'El módulo inicial no puede ser mayor al módulo final.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ];
    }
}
