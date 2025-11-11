# Cálculo de Estadísticas en Dashboard e Informes - Correcciones

## Fecha: 7 de noviembre de 2025

## Problemas Identificados

### 1. Cálculo de Horas Ocupadas (CRÍTICO)

**Problema**: El código actual usa `count()` de reservas como si fuera "horas ocupadas", pero una reserva no equivale a una hora.

**Código Actual (INCORRECTO)**:
```php
$horasOcupadas = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
    ->whereIn('estado', ['activa', 'finalizada'])
    ->count(); // ❌ Esto cuenta RESERVAS, no HORAS
```

**Código Correcto (DEBE CALCULARSE)**:
```php
$horasOcupadas = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
    ->whereIn('estado', ['activa', 'finalizada'])
    ->get()
    ->sum(function($reserva) {
        // Calcular duración real de cada reserva
        if ($reserva->hora && $reserva->hora_salida) {
            $inicio = Carbon::parse($reserva->hora);
            $fin = Carbon::parse($reserva->hora_salida);
            return $inicio->diffInHours($fin, true); // true para decimales
        }
        // Si no hay hora_salida, asumir 1 módulo = 50 minutos
        return 0.83; // 50 minutos / 60
    });
```

### 2. Cálculo de Ocupación Semanal

**Problema**: Divide "número de reservas" entre "total de horas disponibles", lo cual es incorrecto.

**Fórmula Actual (INCORRECTA)**:
```
ocupación% = (número_de_reservas / total_horas_disponibles) × 100
```

**Fórmula Correcta**:
```
ocupación% = (horas_realmente_utilizadas / total_horas_disponibles) × 100

Donde:
- horas_realmente_utilizadas = Σ(hora_salida - hora_entrada) para cada reserva
- total_horas_disponibles = espacios × días_laborales × horas_por_día
```

### 3. Cálculo de Horas Utilizadas Diarias

**Problema**: Retorna número de reservas, no horas reales.

**Código Actual (INCORRECTO)**:
```php
return [
    'utilizadas' => $horasUtilizadas,  // ❌ Es count() de reservas
    'disponibles' => 75 // ❌ Debería ser total de espacios × 15
];
```

**Código Correcto**:
```php
$totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();
$horasPorDia = 15;
$totalHorasDisponibles = $totalEspacios * $horasPorDia;

$horasRealmenteUtilizadas = Reserva::whereDate('fecha_reserva', $hoy)
    ->whereIn('estado', ['activa', 'finalizada'])
    ->get()
    ->sum(function($reserva) {
        if ($reserva->hora && $reserva->hora_salida) {
            $inicio = Carbon::parse($reserva->hora);
            $fin = Carbon::parse($reserva->hora_salida);
            return $inicio->diffInHours($fin, true);
        }
        return 0.83; // 50 min default
    });

return [
    'utilizadas' => round($horasRealmenteUtilizadas, 2),
    'disponibles' => $totalHorasDisponibles
];
```

### 4. Cálculo de Ocupación Mensual

**Problema**: Misma situación que ocupación semanal.

**Corrección Necesaria**: Aplicar la misma lógica de cálculo de horas reales.

### 5. Promedio de Utilización en Reportes

**Problema**: En `ReportController`, se calcula de varias formas diferentes e inconsistentes.

**Línea 63-65 (INCONSISTENTE)**:
```php
$horas_totales_disponibles = $total_espacios * $dias_laborales * 8; // ❌ ¿Por qué 8 horas?
$promedio_utilizacion = $horas_totales_disponibles > 0 ? 
    round(($horas_utilizadas / $horas_totales_disponibles) * 100) : 0;
```

**Debe ser consistente**:
```php
$horas_totales_disponibles = $total_espacios * $dias_laborales * 15; // ✅ 15 módulos por día
```

## Resumen de Cambios Necesarios

### DashboardController.php

1. ✅ **calcularOcupacionSemanal()**: Calcular horas reales en lugar de count()
2. ✅ **calcularOcupacionMensual()**: Calcular horas reales en lugar de count()
3. ✅ **calcularHorasUtilizadas()**: Calcular horas reales y total correcto
4. ✅ **obtenerUsoPorDia()**: Calcular horas reales en lugar de count()
5. ✅ **obtenerEvolucionMensual()**: Calcular horas reales

### ReportController.php

1. ✅ **tipoEspacio()**: Usar 15 horas por día consistentemente
2. ✅ **espacios()**: Usar 15 horas por día consistentemente
3. ✅ **Todos los cálculos de utilización**: Usar horas reales

## Constantes del Sistema

Para mantener consistencia, definir:

```php
const HORAS_POR_DIA_LABORAL = 15;  // 15 módulos
const MINUTOS_POR_MODULO = 50;      // Duración de cada módulo
const DIAS_LABORALES_SEMANA = 5;    // Lunes a Viernes
```

## Pruebas Necesarias

1. Verificar que una reserva de 2 horas se cuenta como 2 horas, no como 1
2. Verificar que el porcentaje de ocupación no supera el 100%
3. Verificar que horas disponibles = espacios × días × 15
4. Verificar consistencia entre dashboard y reportes

## Notas Adicionales

- Se recomienda agregar campo `duracion_horas` en tabla `reservas` para optimizar consultas
- Considerar cache para cálculos pesados
- Agregar tests unitarios para validar fórmulas

## Mejoras Futuras (Code Review)

### Refactorización de Código (Mantenibilidad)

Se agregó un método auxiliar `calcularHorasReserva($reserva)` en `DashboardController.php` pero aún no se está utilizando. Para mejorar la mantenibilidad del código, se recomienda:

**Método helper creado:**
```php
private function calcularHorasReserva($reserva)
{
    if ($reserva->hora && $reserva->hora_salida) {
        $inicio = Carbon::parse($reserva->hora);
        $fin = Carbon::parse($reserva->hora_salida);
        return $inicio->diffInHours($fin, true);
    }
    return 0.83; // 50 minutos
}
```

**Ubicaciones a refactorizar en `DashboardController.php`:**
- `calcularOcupacionSemanal()` - línea ~187
- `calcularOcupacionMensual()` - línea ~274  
- `calcularHorasUtilizadas()` - línea ~330
- `obtenerUsoPorDia()` - línea ~355
- `obtenerEvolucionMensual()` - línea ~498
- Métodos optimizados similares

**Ejemplo de refactorización:**
```php
// Antes (código duplicado):
$horasOcupadas = $reservas->sum(function($reserva) {
    if ($reserva->hora && $reserva->hora_salida) {
        $inicio = Carbon::parse($reserva->hora);
        $fin = Carbon::parse($reserva->hora_salida);
        return $inicio->diffInHours($fin, true);
    }
    return 0.83;
});

// Después (usando helper):
$horasOcupadas = $reservas->sum(function($reserva) {
    return $this->calcularHorasReserva($reserva);
});
```

Esta refactorización:
- ✅ Elimina duplicación de código
- ✅ Facilita futuras modificaciones
- ✅ Asegura consistencia en todos los cálculos
- ✅ Mejora legibilidad

**Nota**: La funcionalidad actual es correcta. Esta refactorización es solo para mejorar mantenibilidad.

