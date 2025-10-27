# Ejemplos de Uso - API Programación Semanal y Asistencia

Este documento contiene ejemplos prácticos de uso de las APIs de programación semanal y registro de asistencia.

## Ejemplos con JavaScript/Fetch

### 1. Consultar Programación Semanal

```javascript
// Función para obtener la programación semanal de una sala
async function obtenerProgramacionSemanal(idEspacio) {
    try {
        const response = await fetch(`/api/programacion-semanal/${idEspacio}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            console.log('Programación obtenida:', data.data);
            return data.data;
        } else {
            console.error('Error:', data.message);
            return null;
        }
    } catch (error) {
        console.error('Error de red:', error);
        return null;
    }
}

// Uso
obtenerProgramacionSemanal('A101').then(programacion => {
    if (programacion) {
        console.log('Espacio:', programacion.espacio.nombre);
        console.log('Período:', programacion.periodo);
        
        // Iterar sobre los días
        Object.entries(programacion.programacion_semanal).forEach(([dia, clases]) => {
            console.log(`\n${dia.toUpperCase()}:`);
            clases.forEach(clase => {
                console.log(`  - ${clase.asignatura.nombre}`);
                console.log(`    Profesor: ${clase.profesor_a_cargo.nombre}`);
                console.log(`    Horario: ${clase.modulos.hora_inicio} - ${clase.modulos.hora_termino}`);
            });
        });
    }
});
```

### 2. Registrar Asistencia

```javascript
// Función para registrar la asistencia de una clase
async function registrarAsistencia(datosAsistencia) {
    try {
        const response = await fetch('/api/asistencia', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(datosAsistencia)
        });

        const data = await response.json();

        if (data.success) {
            console.log('Asistencia registrada:', data.message);
            console.log('Total asistentes:', data.data.total_asistentes);
            return data.data;
        } else {
            console.error('Error:', data.message);
            if (data.errors) {
                console.error('Errores de validación:', data.errors);
            }
            return null;
        }
    } catch (error) {
        console.error('Error de red:', error);
        return null;
    }
}

// Ejemplo de uso con datos completos
const datosClase = {
    id_reserva: 'R202508211455301',
    hora_termino: '10:00:00',
    lista_asistencia: [
        {
            rut: '12345678',
            nombre: 'Juan Pérez García',
            hora_llegada: '08:15:00'
        },
        {
            rut: '87654321',
            nombre: 'María López Silva',
            hora_llegada: '08:10:00'
        },
        {
            rut: '11223344',
            nombre: 'Carlos Rodríguez Muñoz',
            hora_llegada: '08:20:00'
        }
    ],
    contenido_visto: 'Introducción a las derivadas y límites matemáticos'
};

registrarAsistencia(datosClase).then(resultado => {
    if (resultado) {
        console.log('Clase finalizada exitosamente');
        console.log(`Espacio: ${resultado.reserva.espacio}`);
        console.log(`Horario: ${resultado.reserva.hora_inicio} - ${resultado.reserva.hora_termino}`);
    }
});

// Ejemplo sin contenido visto (se guardará "Sin información adicionada")
const datosClaseSinContenido = {
    id_reserva: 'R202508211455302',
    hora_termino: '12:00:00',
    lista_asistencia: [
        {
            rut: '12345678',
            nombre: 'Juan Pérez García',
            hora_llegada: '10:15:00'
        }
    ]
    // contenido_visto no se envía
};

registrarAsistencia(datosClaseSinContenido);
```

## Ejemplos con Axios

### 1. Consultar Programación Semanal

```javascript
import axios from 'axios';

// Función con Axios
async function obtenerProgramacionConAxios(idEspacio) {
    try {
        const { data } = await axios.get(`/api/programacion-semanal/${idEspacio}`);
        
        if (data.success) {
            return data.data;
        }
        
        throw new Error(data.message);
    } catch (error) {
        if (error.response) {
            // Error de respuesta del servidor
            console.error('Error del servidor:', error.response.data.message);
        } else if (error.request) {
            // No se recibió respuesta
            console.error('Sin respuesta del servidor');
        } else {
            // Error en la configuración de la petición
            console.error('Error:', error.message);
        }
        return null;
    }
}
```

### 2. Registrar Asistencia

```javascript
import axios from 'axios';

// Función con Axios
async function registrarAsistenciaConAxios(datosAsistencia) {
    try {
        const { data } = await axios.post('/api/asistencia', datosAsistencia);
        
        if (data.success) {
            console.log('✓ Asistencia registrada');
            console.log(`  Total: ${data.data.total_asistentes} asistentes`);
            return data.data;
        }
        
        throw new Error(data.message);
    } catch (error) {
        if (error.response) {
            if (error.response.status === 422) {
                // Errores de validación
                console.error('Errores de validación:');
                Object.entries(error.response.data.errors).forEach(([campo, errores]) => {
                    console.error(`  ${campo}: ${errores.join(', ')}`);
                });
            } else {
                console.error('Error:', error.response.data.message);
            }
        } else {
            console.error('Error de red:', error.message);
        }
        return null;
    }
}
```

## Ejemplos con PHP/Laravel (Cliente HTTP)

### 1. Consultar Programación Semanal

```php
use Illuminate\Support\Facades\Http;

public function consultarProgramacion($idEspacio)
{
    try {
        $response = Http::get("http://localhost:8000/api/programacion-semanal/{$idEspacio}");
        
        if ($response->successful() && $response->json('success')) {
            $data = $response->json('data');
            
            // Procesar la programación
            foreach ($data['programacion_semanal'] as $dia => $clases) {
                echo "\n{$dia}:\n";
                foreach ($clases as $clase) {
                    echo "  - {$clase['asignatura']['nombre']}\n";
                    echo "    Profesor: {$clase['profesor_a_cargo']['nombre']}\n";
                    echo "    Horario: {$clase['modulos']['hora_inicio']} - {$clase['modulos']['hora_termino']}\n";
                }
            }
            
            return $data;
        }
        
        return null;
    } catch (\Exception $e) {
        \Log::error("Error al consultar programación: " . $e->getMessage());
        return null;
    }
}
```

### 2. Registrar Asistencia

```php
use Illuminate\Support\Facades\Http;

public function registrarAsistencia(array $datosAsistencia)
{
    try {
        $response = Http::post('http://localhost:8000/api/asistencia', $datosAsistencia);
        
        if ($response->successful() && $response->json('success')) {
            $data = $response->json('data');
            
            \Log::info("Asistencia registrada exitosamente", [
                'reserva' => $data['reserva']['id'],
                'total_asistentes' => $data['total_asistentes']
            ]);
            
            return $data;
        }
        
        if ($response->status() === 422) {
            // Errores de validación
            $errores = $response->json('errors');
            \Log::warning("Errores de validación al registrar asistencia", $errores);
            return ['errors' => $errores];
        }
        
        return null;
    } catch (\Exception $e) {
        \Log::error("Error al registrar asistencia: " . $e->getMessage());
        return null;
    }
}

// Ejemplo de uso
$datosAsistencia = [
    'id_reserva' => 'R202508211455301',
    'hora_termino' => '10:00:00',
    'lista_asistencia' => [
        [
            'rut' => '12345678',
            'nombre' => 'Juan Pérez García',
            'hora_llegada' => '08:15:00'
        ],
        [
            'rut' => '87654321',
            'nombre' => 'María López Silva',
            'hora_llegada' => '08:10:00'
        ]
    ],
    'contenido_visto' => 'Introducción a las derivadas'
];

$resultado = $this->registrarAsistencia($datosAsistencia);
```

## Ejemplos con Python (Requests)

### 1. Consultar Programación Semanal

```python
import requests

def obtener_programacion_semanal(id_espacio):
    try:
        url = f"http://localhost:8000/api/programacion-semanal/{id_espacio}"
        response = requests.get(url)
        response.raise_for_status()
        
        data = response.json()
        
        if data['success']:
            programacion = data['data']
            
            print(f"Espacio: {programacion['espacio']['nombre']}")
            print(f"Período: {programacion['periodo']}\n")
            
            for dia, clases in programacion['programacion_semanal'].items():
                print(f"\n{dia.upper()}:")
                for clase in clases:
                    print(f"  - {clase['asignatura']['nombre']}")
                    print(f"    Profesor: {clase['profesor_a_cargo']['nombre']}")
                    print(f"    Horario: {clase['modulos']['hora_inicio']} - {clase['modulos']['hora_termino']}")
            
            return programacion
        
        return None
        
    except requests.exceptions.RequestException as e:
        print(f"Error de red: {e}")
        return None

# Uso
programacion = obtener_programacion_semanal('A101')
```

### 2. Registrar Asistencia

```python
import requests

def registrar_asistencia(datos_asistencia):
    try:
        url = "http://localhost:8000/api/asistencia"
        headers = {'Content-Type': 'application/json'}
        
        response = requests.post(url, json=datos_asistencia, headers=headers)
        response.raise_for_status()
        
        data = response.json()
        
        if data['success']:
            resultado = data['data']
            print(f"✓ Asistencia registrada")
            print(f"  Total asistentes: {resultado['total_asistentes']}")
            print(f"  Espacio: {resultado['reserva']['espacio']}")
            return resultado
        
        return None
        
    except requests.exceptions.HTTPError as e:
        if e.response.status_code == 422:
            # Errores de validación
            errores = e.response.json()['errors']
            print("Errores de validación:")
            for campo, mensajes in errores.items():
                print(f"  {campo}: {', '.join(mensajes)}")
        else:
            print(f"Error HTTP {e.response.status_code}: {e.response.json()['message']}")
        return None
        
    except requests.exceptions.RequestException as e:
        print(f"Error de red: {e}")
        return None

# Ejemplo de uso
datos = {
    'id_reserva': 'R202508211455301',
    'hora_termino': '10:00:00',
    'lista_asistencia': [
        {
            'rut': '12345678',
            'nombre': 'Juan Pérez García',
            'hora_llegada': '08:15:00'
        },
        {
            'rut': '87654321',
            'nombre': 'María López Silva',
            'hora_llegada': '08:10:00'
        }
    ],
    'contenido_visto': 'Introducción a las derivadas'
}

resultado = registrar_asistencia(datos)
```

## Manejo de Errores Comunes

### Error 404 - Espacio o Reserva no encontrada

```javascript
fetch('/api/programacion-semanal/INEXISTENTE')
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Error:', data.message);
            // Mostrar mensaje al usuario: "Espacio no encontrado"
        }
    });
```

### Error 422 - Validación fallida

```javascript
const datosInvalidos = {
    id_reserva: 'R202508211455301',
    hora_termino: 'hora-invalida', // ❌ Formato incorrecto
    lista_asistencia: [] // ❌ Lista vacía
};

registrarAsistencia(datosInvalidos).then(resultado => {
    if (!resultado) {
        console.log('Revise los datos enviados');
    }
});
```

### Error 500 - Error interno del servidor

```javascript
fetch('/api/asistencia', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datos)
})
.then(response => response.json())
.then(data => {
    if (!data.success && response.status === 500) {
        console.error('Error del servidor:', data.error);
        // Contactar al administrador del sistema
    }
});
```

## Integración con Formularios HTML

```html
<!-- Formulario de registro de asistencia -->
<form id="formAsistencia">
    <input type="hidden" name="id_reserva" value="R202508211455301">
    
    <label>Hora de término:</label>
    <input type="time" name="hora_termino" required>
    
    <label>Contenido visto (opcional):</label>
    <textarea name="contenido_visto" rows="3"></textarea>
    
    <div id="listaAsistentes">
        <h4>Lista de Asistentes</h4>
        <div class="asistente">
            <input type="text" name="rut[]" placeholder="RUT sin DV" required>
            <input type="text" name="nombre[]" placeholder="Nombre completo" required>
            <input type="time" name="hora_llegada[]" required>
        </div>
    </div>
    
    <button type="button" onclick="agregarAsistente()">+ Agregar Asistente</button>
    <button type="submit">Registrar Asistencia</button>
</form>

<script>
document.getElementById('formAsistencia').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    // Construir el objeto de datos
    const datos = {
        id_reserva: formData.get('id_reserva'),
        hora_termino: formData.get('hora_termino') + ':00', // Agregar segundos
        contenido_visto: formData.get('contenido_visto') || null,
        lista_asistencia: []
    };
    
    // Construir lista de asistentes
    const ruts = formData.getAll('rut[]');
    const nombres = formData.getAll('nombre[]');
    const horas = formData.getAll('hora_llegada[]');
    
    for (let i = 0; i < ruts.length; i++) {
        datos.lista_asistencia.push({
            rut: ruts[i],
            nombre: nombres[i],
            hora_llegada: horas[i] + ':00'
        });
    }
    
    // Enviar los datos
    const resultado = await registrarAsistencia(datos);
    
    if (resultado) {
        alert('Asistencia registrada exitosamente');
        window.location.href = '/dashboard';
    } else {
        alert('Error al registrar la asistencia');
    }
});
</script>
```

## Tips y Mejores Prácticas

1. **Validación del lado del cliente**: Antes de enviar los datos, valide el formato de las horas y que la lista de asistencia no esté vacía.

2. **Manejo de errores**: Siempre maneje los diferentes códigos de estado HTTP y proporcione mensajes claros al usuario.

3. **Contenido opcional**: El campo `contenido_visto` es opcional. Si no tiene información, puede omitirlo o enviarlo como `null`.

4. **Formato de horas**: Asegúrese de enviar las horas en formato de 24 horas (HH:MM:SS).

5. **RUT sin dígito verificador**: El campo `rut` debe contener solo números, sin el dígito verificador ni guiones.

6. **Transacciones**: El endpoint de registro de asistencia usa transacciones, por lo que si algo falla, todos los cambios se revierten.
