# Comando: Crear Usuarios para Profesores

## Descripción
Este comando Artisan crea automáticamente cuentas de usuario en la tabla `users` para todos los profesores registrados en la tabla `profesors`.

## Ubicación
`app/Console/Commands/CrearUsuariosProfesores.php`

## Uso

### Modo de prueba (recomendado primero)
```bash
php artisan crear:usuarios-profesores --dry-run
```
Este modo simula la creación sin hacer cambios reales en la base de datos.

### Modo de ejecución real
```bash
php artisan crear:usuarios-profesores
```
Este modo crea realmente los usuarios en la base de datos.

## Requisitos Previos

1. **Profesores cargados**: Debe haber profesores en la tabla `profesors` (normalmente cargados mediante la funcionalidad de carga masiva del sistema).

2. **RUT válidos**: Los profesores deben tener RUT válidos en el campo `run_profesor`.

## Funcionamiento

### Credenciales generadas:
- **Username (run)**: RUT completo del profesor (ej: `12345678-9`)
- **Password**: RUT sin dígito verificador (ej: `12345678`)

### Características:
- ✅ Detecta y omite profesores que ya tienen usuario creado
- ✅ Valida formato de RUT antes de crear usuario
- ✅ Hashea las contraseñas usando bcrypt
- ✅ Registra errores en el log de Laravel
- ✅ Muestra progreso y estadísticas al finalizar

### Ejemplo de salida:
```
Iniciando creación de usuarios para profesores...
✓ Usuario creado para profesor: Juan Pérez (12345678-9)
⚠ Profesor ya tiene usuario: María González (87654321-0)
✗ RUT inválido para profesor: Ana López (123)

Proceso finalizado. Creados: 1. Omitidos: 2.
```

## Casos de Uso

1. **Primera implementación**: Después de migrar datos de profesores, ejecutar este comando para crear todas las cuentas de usuario.

2. **Carga incremental**: Si se agregan nuevos profesores, ejecutar el comando para crear solo los usuarios faltantes.

3. **Auditoría**: Usar `--dry-run` para verificar qué usuarios se crearían sin hacer cambios.

## Logs
Los errores y eventos importantes se registran en `storage/logs/laravel.log` con el prefijo `[CrearUsuariosProfesores]`.

## Notas Técnicas
- El comando usa el modelo `Profesor` para leer datos
- Crea registros en el modelo `User` 
- No asigna roles automáticamente (se pueden asignar después)
- Es seguro ejecutar múltiples veces (no duplica usuarios)