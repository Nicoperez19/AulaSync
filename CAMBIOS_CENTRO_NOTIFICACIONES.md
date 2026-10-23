# Centro de Notificaciones – cambios

Nuevos:
- app/Livewire/NotificacionesCenter.php
- resources/views/livewire/notificaciones-center.blade.php
- resources/views/notificaciones/index.blade.php
- database/migrations/tenant/2026_10_22_000001_ensure_notificaciones_table_all_sedes.php

Modificados:
- app/Models/Notificacion.php (tipos, URL/mensaje dinámicos, notificación de Soporte, eliminarLeidas)
- app/Livewire/NotificacionesDropdown.php + vista (usa helpers del modelo, enlace al Centro)
- app/Http/Controllers/SupportTicketController.php (notifica respuestas de Soporte)
- routes/web.php (ruta /notificaciones -> notificaciones.index)
- config/multitenancy.php (tabla notificaciones en tenant_tables)

Después de copiar:
  php artisan tenants:migrate     # crea la tabla en las 5 sedes si faltara
  npm run build                   # compila las clases Tailwind nuevas
  php artisan view:clear

ServBay:
- .env.servbay (root / ServBay.dev, APP_URL https://aulasync.local.cl)
- servbay_crear_bases.php (crea aulamanager_central, _th, _ch, _la, _ct, _ccp)
- instalar_servbay.bat (doble clic: configura .env, crea BDs, migra, siembra y compila)
