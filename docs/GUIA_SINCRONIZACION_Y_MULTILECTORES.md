# Guía Técnica: Sincronización Real-Time y Soporte Multi-Lectores

Esta guía documenta los pasos necesarios para implementar la sincronización en tiempo real y el soporte para múltiples estaciones de escaneo QR en el Plano Digital de AulaSync.

## 1. Descubrimiento Dinámico de Lectores (Frontend)

Actualmente, el sistema gestiona 4 tipos de entrada (main, devolución, solicitud, sala de estudio). Para que el sistema sea escalable a cualquier cantidad de lectores o contextos sin tocar el JavaScript, se recomienda la siguiente refactorización en `show.blade.php`:

### Refactorización de `QRInputManager`
En lugar de definir los inputs manualmente, la clase debe descubrirlos en el DOM:

```javascript
class QRInputManager {
    constructor() {
        this.qrInputs = {};
        this.discoverInputs();
        this.activeInput = null;
        this.init();
    }

    discoverInputs() {
        // Busca todos los inputs que sigan el patrón de ID qr-input*
        const inputs = document.querySelectorAll('input[id^="qr-input"]');
        inputs.forEach(input => {
            // El ID 'qr-input' se mapea a 'main', el resto usa su sufijo (ej: 'qr-input-devolucion' -> 'devolucion')
            const type = input.id === 'qr-input' ? 'main' : input.id.replace('qr-input-', '');
            this.qrInputs[type] = input;
        });
    }
    // ... resto de métodos ...
}
```

### Cómo añadir un nuevo lector
Para añadir un nuevo contexto de escaneo (ej: `entrega-equipos`):
1. Añadir el input en el HTML: `<input type="text" id="qr-input-entrega-equipos" class="qr-input-field ...">`.
2. El `QRInputManager` lo detectará automáticamente.
3. Podrás activarlo con `qrInputManager.setActiveInput('entrega-equipos')`.

---

## 2. Sincronización en Tiempo Real (WebSockets)

Para que todas las estaciones de escaneo vean los cambios de estado instantáneamente (sin esperar al polling de 5 segundos), se debe implementar el sistema de Broadcasting.

### Paso A: Crear el Evento en el Backend
Generar un evento que transmita los cambios en los bloques del mapa:

```php
// app/Events/SpaceStatusChanged.php
class SpaceStatusChanged implements ShouldBroadcast
{
    public $mapId;
    public $spaceId;
    public $newStatus;

    public function broadcastOn() {
        return new Channel('map.' . $this->mapId);
    }

    public function broadcastAs() {
        return 'space.updated';
    }
}
```

### Paso B: Disparar el Evento
En los controladores (`PlanoDigitalController` y `QuickActionsController`), disparar el evento tras cada operación exitosa de reserva o devolución:

```php
event(new SpaceStatusChanged($mapaId, $idEspacio, 'Disponible'));
```

### Paso C: Escuchar en el Frontend
En `show.blade.php`, suscribirse al canal del mapa usando Laravel Echo:

```javascript
// Dentro de la inicialización de JS
window.Echo.channel(`map.${mapaId}`)
    .listen('.space.updated', (data) => {
        console.log('Cambio detectado en espacio:', data.spaceId);
        // Forzar la actualización del mapa ignorando el temporizador del polling
        if (typeof actualizarColoresEspacios === 'function') {
            actualizarColoresEspacios(true);
        }
    });
```

---

## 3. Ventajas de esta Implementación

1.  **Escalabilidad**: Puedes tener 10 o 50 estaciones de escaneo QR funcionando simultáneamente.
2.  **Eficiencia**: Se reduce la carga en el servidor ya que el polling puede hacerse menos frecuente (ej: cada 30s) delegando la actualización crítica a los WebSockets.
3.  **Experiencia de Usuario**: Los cambios de estado son instantáneos en todas las pantallas del campus, eliminando la confusión de ver una sala ocupada que acaba de ser liberada en otra estación.
