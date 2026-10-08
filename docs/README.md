# Documentación Oficial — AulaSync | Sistema de Información de Aulas (SIA)

Esta carpeta reúne la documentación centralizada de AulaSync, organizada en dos manuales maestros para facilitar su consulta y mantenimiento.

---

## Estructura de Documentación

```text
docs/
├── MANUAL.md                     <-- Manual de Usuario Oficial (Integrado en la app web /ayuda)
├── MANUAL_TECNICO.md             <-- Manual Técnico, APIs y Arquitectura del Sistema
├── README.md                     <-- Índice general y Guía de Instalación
├── ResumenCalculosAulaSync.pdf   <-- Informe de fórmulas y cálculos de ocupación
├── ejemplos/                     <-- Payloads JSON de ejemplo para endpoints de asistencia
├── postman/                      <-- Colección Postman para pruebas de integración de APIs
└── image-*.png                   <-- Capturas de pantalla e infografías de los manuales
```

---

## 1. Documentos Principales

* **[Manual de Usuario (MANUAL.md)](MANUAL.md):**  
  Guía funcional completa para Administradores, Supervisores, Control Docente y Docentes. Estructurado en 9 capítulos que cubren desde el acceso multisede, plano digital interactivo, asistencia por códigos QR, reservas, gestión docente, analítica de dashboard y mantenedores.
  
  > *Nota técnica:* Este archivo es procesado y servido dinámicamente por la aplicación en la ruta web `/ayuda` a través del controlador `ManualController`.

* **[Manual Técnico y de Arquitectura (MANUAL_TECNICO.md)](MANUAL_TECNICO.md):**  
  Referencia técnica para desarrolladores y administradores de infraestructura (DevOps). Documenta:
  * Arquitectura Multitenancy por subdominio y aislamiento de base de datos.
  * Servidor de WebSockets en tiempo real con **Laravel Reverb**.
  * Catálogo completo de endpoints REST (Asistencia docente, Asistencia de alumnos por QR, Espacios).
  * Programación de tareas automáticas (Scheduler, auto-finalización de reservas, período de gracia).
  * Sistema de colas, notificaciones y correos masivos con variables dinámicas.
  * Comandos de consola y mantenimiento de períodos académicos.

---

## 2. Guía de Instalación y Despliegue Rápido

1. **Clonar repositorio:**
   ```bash
   git clone https://github.com/Nicoperez19/AulaSync.git
   cd AulaSync
   ```

2. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   ```

3. **Crear directorios de almacenamiento requeridos:**
   ```bash
   mkdir storage\framework\cache
   mkdir storage\framework\sessions
   mkdir storage\framework\views
   mkdir bootstrap\cache
   ```

4. **Instalar dependencias y generar clave de aplicación:**
   ```bash
   composer install
   npm install
   php artisan key:generate
   ```

5. **Migraciones y Seeders de Base de Datos Central:**
   ```bash
   php artisan migrate --path=database/migrations/central
   php artisan db:seed --class=CentralDatabaseSeeder
   ```

6. **Migraciones y Seeders de Tenants (Sedes):**
   ```bash
   php artisan tenants:setup --fresh --seed
   ```

7. **Compilar recursos frontend:**
   ```bash
   npm run build
   # O en modo desarrollo:
   npm run dev
   ```

8. **Iniciar servicios locales:**
   ```bash
   # Servidor HTTP Laravel:
   php artisan serve

   # Servidor de WebSockets (Reverb):
   php artisan reverb:start

   # Procesador de colas (correos y tareas en segundo plano):
   php artisan queue:work
   ```
