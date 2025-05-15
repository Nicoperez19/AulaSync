<?php

namespace App\Imports;

use App\Models\User;
use App\Models\DataLoad;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $dataLoad;
    protected $registrosProcesados = 0;

    public function __construct(DataLoad $dataLoad)
    {
        $this->dataLoad = $dataLoad;
    }

    public function model(array $row)
    {
        $this->registrosProcesados++;

        return new User([
            'run' => $row['run'],
            'name' => $row['nombre'],
            'email' => $row['correo'],
            'celular' => $row['celular'],
            'direccion' => $row['direccion'],
            'fecha_nacimiento' => $row['fecha_nacimiento'],
            'anio_ingreso' => $row['anio_ingreso'],
            'password' => Hash::make($row['run']), // Contraseña por defecto es el RUN
        ]);
    }

    public function rules(): array
    {
        return [
            'run' => 'required|unique:users,run',
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|unique:users,email',
            'celular' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'anio_ingreso' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ];
    }

    public function customValidationMessages()
    {
        return [
            'run.required' => 'El RUN es obligatorio',
            'run.unique' => 'El RUN ya existe en el sistema',
            'nombre.required' => 'El nombre es obligatorio',
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'El correo debe ser válido',
            'correo.unique' => 'El correo ya existe en el sistema',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida',
            'anio_ingreso.required' => 'El año de ingreso es obligatorio',
            'anio_ingreso.integer' => 'El año de ingreso debe ser un número entero',
            'anio_ingreso.min' => 'El año de ingreso no puede ser anterior a 1900',
            'anio_ingreso.max' => 'El año de ingreso no puede ser posterior al año actual',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function __destruct()
    {
        // Actualizar el estado de la carga
        $this->dataLoad->update([
            'estado' => 'completado',
            'registros_cargados' => $this->registrosProcesados
        ]);
    }
} 