# Multi-Tenancy Integration for Bulk Data Loading

## Overview
This document explains how the multi-tenancy system integrates with bulk data loading operations, specifically for semester schedule imports.

## Current Implementation Issue
The current `DataLoadController` has hardcoded filtering:

```php
if (strtolower(trim($sede)) !== 'talcahuano') {
    $skippedRows++;
    continue;
}
```

## Multi-Tenancy Solution

With multi-tenancy enabled, the filtering happens automatically based on the current tenant. Here's how to update the code:

### Before (Hardcoded)
```php
// Old approach - hardcoded filtering
$sede = $row[7];
if (strtolower(trim($sede)) !== 'talcahuano') {
    $skippedRows++;
    continue;
}
```

### After (Multi-Tenant)
```php
// New approach - automatic tenant filtering
$tenant = tenant();

// Option 1: Filter by sede name
$sedeNombre = $row[7];
if ($tenant && $tenant->sede) {
    if (strtolower(trim($sedeNombre)) !== strtolower(trim($tenant->sede->nombre_sede))) {
        $skippedRows++;
        Log::info("Skipping row for different sede: {$sedeNombre}");
        continue;
    }
}

// Option 2: Filter by space prefix (recommended)
$idEspacio = $row[X]; // Replace X with actual column index
if ($tenant && $tenant->prefijo_espacios) {
    if (!str_starts_with($idEspacio, $tenant->prefijo_espacios)) {
        $skippedRows++;
        Log::info("Skipping space with wrong prefix: {$idEspacio}");
        continue;
    }
}
```

## Automatic Tenant Scoping

Thanks to the `BelongsToTenant` trait, the following operations are automatically scoped:

### 1. Espacio Queries
```php
// This automatically filters by tenant's space prefix
$espacio = Espacio::where('id_espacio', $idEspacio)->first();
```

### 2. Profesor Queries
```php
// This automatically filters by tenant's sede_id
$profesor = Profesor::where('run_profesor', $runProfesor)->first();
```

### 3. Creating Planificaciones
```php
// When creating, the tenant context is preserved
Planificacion_Asignatura::create([
    'id_asignatura' => $idAsignatura,
    'id_horario' => $idHorario,
    'id_modulo' => $idModulo,
    'id_espacio' => $idEspacio,
    'inscritos' => $inscritos,
]);
// The espacio and profesor relationships will enforce tenant isolation
```

## Updated DataLoadController Example

Here's a recommended approach for the upload method:

```php
public function upload(Request $request)
{
    set_time_limit(300);

    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        'semestre_selector' => 'required|in:1,2'
    ]);

    // Get current tenant
    $tenant = tenant();
    if (!$tenant) {
        return redirect()->back()->with('error', 'No se pudo identificar la sede actual.');
    }

    $semestreSeleccionado = $request->input('semestre_selector');
    $anioActual = date('Y');
    $periodoSeleccionado = $anioActual . '-' . $semestreSeleccionado;

    try {
        $file = $request->file('file');
        // ... file storage code ...

        $rows = Excel::toArray([], $file)[0];
        $processedCount = 0;
        $skippedRows = 0;
        $errors = [];

        // Clean previous planificaciones for this period and tenant
        // This is automatically scoped to current tenant
        $horariosDelPeriodo = Horario::where('periodo', $periodoSeleccionado)->pluck('id_horario');
        $planificacionesEliminadas = Planificacion_Asignatura::whereIn('id_horario', $horariosDelPeriodo)->delete();
        
        Log::info("Tenant {$tenant->name}: Cleaned {$planificacionesEliminadas} planificaciones for period {$periodoSeleccionado}");

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip headers

            try {
                // Extract data from row
                $idEspacio = $row[X]; // Replace with actual column
                $runProfesor = $row[Y]; // Replace with actual column
                $sedeNombre = $row[Z]; // Replace with actual column

                // Filter by tenant's sede
                if ($tenant->sede && strtolower(trim($sedeNombre)) !== strtolower(trim($tenant->sede->nombre_sede))) {
                    $skippedRows++;
                    continue;
                }

                // Validate space belongs to tenant (by prefix)
                if ($tenant->prefijo_espacios && !str_starts_with($idEspacio, $tenant->prefijo_espacios)) {
                    $skippedRows++;
                    Log::warning("Skipped space {$idEspacio} - doesn't match prefix {$tenant->prefijo_espacios}");
                    continue;
                }

                // Find or create profesor (automatically scoped to tenant)
                $profesor = Profesor::firstOrCreate(
                    ['run_profesor' => $runProfesor],
                    [
                        'name' => $nombreProfesor,
                        'email' => $emailProfesor,
                        'sede_id' => $tenant->sede_id, // Explicitly set tenant's sede
                        // ... other fields ...
                    ]
                );

                // Find espacio (automatically scoped to tenant)
                $espacio = Espacio::where('id_espacio', $idEspacio)->first();
                
                if (!$espacio) {
                    Log::warning("Space {$idEspacio} not found for tenant {$tenant->name}");
                    $skippedRows++;
                    continue;
                }

                // Create planificacion (automatically linked to tenant through relationships)
                Planificacion_Asignatura::create([
                    'id_asignatura' => $idAsignatura,
                    'id_horario' => $idHorario,
                    'id_modulo' => $idModulo,
                    'id_espacio' => $idEspacio,
                    'inscritos' => $inscritos,
                ]);

                $processedCount++;

            } catch (\Exception $e) {
                Log::error("Error processing row {$index}: " . $e->getMessage());
                $errors[] = "Row {$index}: " . $e->getMessage();
            }
        }

        // Update DataLoad with results
        $dataLoad->update([
            'estado' => empty($errors) ? 'completado' : 'completado_con_errores',
            'registros_cargados' => $processedCount,
            'observaciones' => "Procesados: {$processedCount}, Omitidos: {$skippedRows}, Errores: " . count($errors)
        ]);

        Log::info("Tenant {$tenant->name}: Data load completed - {$processedCount} records processed, {$skippedRows} skipped");

        return redirect()->route('data.index')->with('success', 'Datos cargados exitosamente');

    } catch (\Exception $e) {
        Log::error('Error in data upload: ' . $e->getMessage());
        if (isset($dataLoad)) {
            $dataLoad->update(['estado' => 'error']);
        }
        return redirect()->back()->with('error', 'Error al cargar los datos: ' . $e->getMessage());
    }
}
```

## Key Points

### 1. Tenant Context
Always check for current tenant at the start:
```php
$tenant = tenant();
if (!$tenant) {
    return redirect()->back()->with('error', 'Tenant not identified');
}
```

### 2. Logging
Include tenant information in logs:
```php
Log::info("Tenant {$tenant->name}: Operation completed");
```

### 3. Data Validation
Validate that imported data belongs to current tenant:
```php
// By sede name
if ($tenant->sede && $sedeNombre !== $tenant->sede->nombre_sede) {
    // Skip or error
}

// By space prefix
if ($tenant->prefijo_espacios && !str_starts_with($idEspacio, $tenant->prefijo_espacios)) {
    // Skip or error
}
```

### 4. Explicit Sede Assignment
When creating profesors, explicitly set the sede:
```php
Profesor::create([
    'run_profesor' => $run,
    'sede_id' => $tenant->sede_id, // Explicit assignment
    // ... other fields
]);
```

### 5. Error Handling
Provide tenant-specific error messages:
```php
if (!$espacio) {
    Log::warning("Tenant {$tenant->name}: Space {$idEspacio} not found");
    continue;
}
```

## Benefits

1. **Automatic Filtering**: Queries are automatically scoped to current tenant
2. **Data Isolation**: Each sede only sees and imports its own data
3. **No Hardcoded Values**: No more hardcoded sede names like "talcahuano"
4. **Scalability**: Easy to add new sedes without code changes
5. **Audit Trail**: Clear logging of which tenant performed which operation

## Migration Path

To migrate existing code:

1. Remove hardcoded sede filters
2. Add tenant context retrieval
3. Use tenant properties for validation
4. Trust the automatic scoping for queries
5. Explicitly set sede_id when creating records

## Testing

Test with multiple tenants:

```php
// Setup
$tenant1 = Tenant::create(['domain' => 'sede1', 'sede_id' => 'SEDE001', 'prefijo_espacios' => 'S1']);
$tenant2 = Tenant::create(['domain' => 'sede2', 'sede_id' => 'SEDE002', 'prefijo_espacios' => 'S2']);

// Test tenant 1
$tenant1->makeCurrent();
$this->uploadData($file1);
$planificaciones1 = Planificacion_Asignatura::count();

// Test tenant 2
$tenant2->makeCurrent();
$this->uploadData($file2);
$planificaciones2 = Planificacion_Asignatura::count();

// Verify isolation
$this->assertNotEquals($planificaciones1, $planificaciones2);
```

## Recommendations

1. Always validate tenant at the start of bulk operations
2. Log tenant information for debugging
3. Use tenant prefix for space validation
4. Explicitly set sede_id when creating records
5. Trust the automatic scoping but validate results
6. Include tenant context in error messages
