# Guía de Integración - Cliente Tauri (Escáner de Asistencia)

## 📱 Descripción

Esta guía muestra cómo integrar el sistema de registro de asistencia con una aplicación Tauri que escanea códigos de barras o QR de estudiantes.

---

## 🏗️ Arquitectura del Cliente

```
┌─────────────────────────────────────┐
│      Aplicación Tauri (Rust)       │
│                                     │
│  ┌───────────────────────────────┐ │
│  │   Frontend (HTML/JS/React)    │ │
│  │   - UI del escáner            │ │
│  │   - Feedback visual           │ │
│  │   - Gestión de estado         │ │
│  └───────────────────────────────┘ │
│                                     │
│  ┌───────────────────────────────┐ │
│  │   Backend Tauri (Rust)        │ │
│  │   - Comunicación con API      │ │
│  │   - Gestión de configuración  │ │
│  │   - Manejo de escáner         │ │
│  └───────────────────────────────┘ │
└─────────────────────────────────────┘
              ↓ HTTP
┌─────────────────────────────────────┐
│    API Laravel (AulaSync)           │
│    POST /api/attendance             │
└─────────────────────────────────────┘
```

---

## 🔧 Implementación en JavaScript/TypeScript (Frontend Tauri)

### 1. Configuración del Cliente API

```typescript
// src/services/attendanceApi.ts

interface AttendanceRequest {
  student_id: string;
  room_id?: string;
  reservation_id?: string;
  student_name?: string;
}

interface AttendanceResponse {
  success: boolean;
  message: string;
  data?: {
    attendance: {
      id: number;
      student_id: string;
      student_name: string;
      arrival_time: string;
      registered_at: string;
    };
    reservation: {
      id: string;
      room_id: string;
      room_name: string;
      date: string;
      start_time: string;
      type: string;
      instructor: {
        type: string;
        name: string;
        id: string;
      };
    };
    occupancy: {
      current: number;
      capacity: number;
    };
  };
  errors?: Record<string, string[]>;
  error?: string;
}

class AttendanceApiClient {
  private baseUrl: string;
  private timeout: number;

  constructor(baseUrl: string = 'http://localhost:8000', timeout: number = 10000) {
    this.baseUrl = baseUrl;
    this.timeout = timeout;
  }

  /**
   * Registrar asistencia de un estudiante
   */
  async registerAttendance(
    studentId: string, 
    roomId: string, 
    studentName?: string
  ): Promise<AttendanceResponse> {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.timeout);

    try {
      const response = await fetch(`${this.baseUrl}/api/attendance`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          student_id: studentId,
          room_id: roomId,
          student_name: studentName,
        }),
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      const data = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Error al registrar asistencia',
          errors: data.errors,
          error: data.error,
        };
      }

      return data;
    } catch (error) {
      clearTimeout(timeoutId);

      if (error instanceof Error) {
        if (error.name === 'AbortError') {
          return {
            success: false,
            message: 'Tiempo de espera agotado. Verifica tu conexión.',
          };
        }
        return {
          success: false,
          message: `Error de red: ${error.message}`,
        };
      }

      return {
        success: false,
        message: 'Error desconocido al conectar con el servidor',
      };
    }
  }

  /**
   * Obtener asistencias de una reserva
   */
  async getReservationAttendances(reservationId: string): Promise<any> {
    try {
      const response = await fetch(
        `${this.baseUrl}/api/attendance/reservation/${reservationId}`,
        {
          headers: {
            'Accept': 'application/json',
          },
        }
      );

      return await response.json();
    } catch (error) {
      console.error('Error obteniendo asistencias:', error);
      return null;
    }
  }

  /**
   * Verificar conectividad con el servidor
   */
  async checkConnection(): Promise<boolean> {
    try {
      const response = await fetch(`${this.baseUrl}/api/health`, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
      });
      return response.ok;
    } catch {
      return false;
    }
  }

  /**
   * Cambiar URL base del API
   */
  setBaseUrl(url: string): void {
    this.baseUrl = url.replace(/\/$/, ''); // Remover trailing slash
  }
}

export default new AttendanceApiClient();
```

---

### 2. Componente React del Escáner

```tsx
// src/components/AttendanceScanner.tsx

import React, { useState, useEffect, useRef } from 'react';
import attendanceApi from '../services/attendanceApi';

interface ScannerConfig {
  roomId: string;
  roomName: string;
  apiUrl: string;
}

const AttendanceScanner: React.FC = () => {
  const [config, setConfig] = useState<ScannerConfig | null>(null);
  const [scanning, setScanning] = useState(false);
  const [lastScan, setLastScan] = useState<string>('');
  const [status, setStatus] = useState<'idle' | 'success' | 'error'>('idle');
  const [message, setMessage] = useState<string>('');
  const [stats, setStats] = useState({ total: 0, current: 0, capacity: 0 });
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    // Cargar configuración guardada
    const savedConfig = localStorage.getItem('scanner_config');
    if (savedConfig) {
      const parsed = JSON.parse(savedConfig);
      setConfig(parsed);
      attendanceApi.setBaseUrl(parsed.apiUrl);
    }

    // Auto-focus en el input del escáner
    inputRef.current?.focus();
  }, []);

  const handleScan = async (studentId: string) => {
    if (!config) {
      setStatus('error');
      setMessage('Configuración no encontrada. Configure el escáner primero.');
      return;
    }

    if (scanning) {
      return; // Prevenir múltiples escaneos simultáneos
    }

    setScanning(true);
    setStatus('idle');
    setLastScan(studentId);

    try {
      const response = await attendanceApi.registerAttendance(
        studentId,
        config.roomId
      );

      if (response.success && response.data) {
        setStatus('success');
        setMessage(
          `✓ ${response.data.attendance.student_name} - Asistencia registrada`
        );
        
        // Actualizar estadísticas
        setStats({
          total: stats.total + 1,
          current: response.data.occupancy.current,
          capacity: response.data.occupancy.capacity,
        });

        // Reproducir sonido de éxito
        playSuccessSound();
        
        // Limpiar mensaje después de 3 segundos
        setTimeout(() => {
          setStatus('idle');
          setMessage('');
        }, 3000);
      } else {
        setStatus('error');
        setMessage(`✗ ${response.message}`);
        playErrorSound();
        
        // Limpiar mensaje después de 5 segundos
        setTimeout(() => {
          setStatus('idle');
          setMessage('');
        }, 5000);
      }
    } catch (error) {
      setStatus('error');
      setMessage('✗ Error de conexión con el servidor');
      playErrorSound();
    } finally {
      setScanning(false);
      inputRef.current?.focus();
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    
    // Cuando el escáner termina de leer (generalmente incluye un Enter)
    if (value.includes('\n') || value.includes('\r')) {
      const studentId = value.trim();
      if (studentId) {
        handleScan(studentId);
        e.target.value = ''; // Limpiar input
      }
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      const value = (e.target as HTMLInputElement).value.trim();
      if (value) {
        handleScan(value);
        (e.target as HTMLInputElement).value = '';
      }
    }
  };

  const playSuccessSound = () => {
    const audio = new Audio('/sounds/success.mp3');
    audio.volume = 0.5;
    audio.play().catch(console.error);
  };

  const playErrorSound = () => {
    const audio = new Audio('/sounds/error.mp3');
    audio.volume = 0.5;
    audio.play().catch(console.error);
  };

  const saveConfiguration = (newConfig: ScannerConfig) => {
    localStorage.setItem('scanner_config', JSON.stringify(newConfig));
    setConfig(newConfig);
    attendanceApi.setBaseUrl(newConfig.apiUrl);
  };

  if (!config) {
    return (
      <ConfigurationForm onSave={saveConfiguration} />
    );
  }

  return (
    <div className="scanner-container">
      {/* Header con información de sala */}
      <div className="header">
        <h1>Escáner de Asistencia</h1>
        <div className="room-info">
          <h2>{config.roomName}</h2>
          <span className="room-id">ID: {config.roomId}</span>
        </div>
      </div>

      {/* Estadísticas */}
      <div className="stats">
        <div className="stat-card">
          <div className="stat-value">{stats.current}</div>
          <div className="stat-label">Presentes</div>
        </div>
        <div className="stat-card">
          <div className="stat-value">{stats.total}</div>
          <div className="stat-label">Registrados Hoy</div>
        </div>
        <div className="stat-card">
          <div className="stat-value">
            {stats.capacity ? `${Math.round((stats.current / stats.capacity) * 100)}%` : '-'}
          </div>
          <div className="stat-label">Ocupación</div>
        </div>
      </div>

      {/* Input del escáner (oculto, solo para capturar) */}
      <input
        ref={inputRef}
        type="text"
        className="scanner-input"
        onChange={handleInputChange}
        onKeyDown={handleKeyDown}
        placeholder="Escanee el código del estudiante..."
        autoFocus
      />

      {/* Área de feedback visual */}
      <div className={`feedback-area ${status}`}>
        {scanning && (
          <div className="scanning-indicator">
            <div className="spinner"></div>
            <p>Procesando...</p>
          </div>
        )}
        
        {!scanning && status !== 'idle' && (
          <div className={`message ${status}`}>
            {message}
          </div>
        )}

        {!scanning && status === 'idle' && (
          <div className="ready-state">
            <div className="scan-icon">📷</div>
            <p>Listo para escanear</p>
            {lastScan && (
              <p className="last-scan">Último ID: {lastScan}</p>
            )}
          </div>
        )}
      </div>

      {/* Botón de configuración */}
      <button 
        className="config-button"
        onClick={() => setConfig(null)}
      >
        ⚙️ Configuración
      </button>
    </div>
  );
};

// Componente de configuración
const ConfigurationForm: React.FC<{ onSave: (config: ScannerConfig) => void }> = ({ onSave }) => {
  const [roomId, setRoomId] = useState('');
  const [roomName, setRoomName] = useState('');
  const [apiUrl, setApiUrl] = useState('http://localhost:8000');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSave({ roomId, roomName, apiUrl });
  };

  return (
    <form className="config-form" onSubmit={handleSubmit}>
      <h2>Configuración del Escáner</h2>
      
      <div className="form-group">
        <label htmlFor="apiUrl">URL del API</label>
        <input
          id="apiUrl"
          type="text"
          value={apiUrl}
          onChange={(e) => setApiUrl(e.target.value)}
          placeholder="http://localhost:8000"
          required
        />
      </div>

      <div className="form-group">
        <label htmlFor="roomId">ID de la Sala</label>
        <input
          id="roomId"
          type="text"
          value={roomId}
          onChange={(e) => setRoomId(e.target.value)}
          placeholder="A101"
          required
        />
      </div>

      <div className="form-group">
        <label htmlFor="roomName">Nombre de la Sala</label>
        <input
          id="roomName"
          type="text"
          value={roomName}
          onChange={(e) => setRoomName(e.target.value)}
          placeholder="Sala A101"
          required
        />
      </div>

      <button type="submit">Guardar Configuración</button>
    </form>
  );
};

export default AttendanceScanner;
```

---

### 3. Estilos CSS

```css
/* src/styles/scanner.css */

.scanner-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.room-info {
  background: rgba(255, 255, 255, 0.2);
  padding: 1rem;
  border-radius: 10px;
  backdrop-filter: blur(10px);
}

.room-info h2 {
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
}

.room-id {
  font-size: 1rem;
  opacity: 0.8;
}

.stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.2);
  padding: 1.5rem;
  border-radius: 10px;
  text-align: center;
  backdrop-filter: blur(10px);
}

.stat-value {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 1rem;
  opacity: 0.9;
}

.scanner-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.feedback-area {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  backdrop-filter: blur(10px);
  padding: 3rem;
  transition: all 0.3s ease;
}

.feedback-area.success {
  background: rgba(72, 187, 120, 0.3);
  border: 3px solid #48bb78;
}

.feedback-area.error {
  background: rgba(245, 101, 101, 0.3);
  border: 3px solid #f56565;
}

.scanning-indicator {
  text-align: center;
}

.spinner {
  width: 80px;
  height: 80px;
  border: 8px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.message {
  font-size: 2rem;
  font-weight: bold;
  text-align: center;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.ready-state {
  text-align: center;
}

.scan-icon {
  font-size: 5rem;
  margin-bottom: 1rem;
}

.ready-state p {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.last-scan {
  font-size: 1rem;
  opacity: 0.7;
}

.config-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1rem;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.config-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.config-form {
  max-width: 500px;
  margin: auto;
  background: white;
  color: #333;
  padding: 2rem;
  border-radius: 20px;
}

.config-form h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: #667eea;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

.config-form button {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.config-form button:hover {
  transform: scale(1.02);
}
```

---

## 🦀 Implementación en Rust (Backend Tauri)

```rust
// src-tauri/src/main.rs

#[tauri::command]
async fn register_attendance(
    student_id: String,
    room_id: String,
    api_url: String,
) -> Result<String, String> {
    let client = reqwest::Client::new();
    
    let body = serde_json::json!({
        "student_id": student_id,
        "room_id": room_id,
    });

    let response = client
        .post(format!("{}/api/attendance", api_url))
        .header("Content-Type", "application/json")
        .header("Accept", "application/json")
        .json(&body)
        .send()
        .await
        .map_err(|e| format!("Error de red: {}", e))?;

    let response_text = response
        .text()
        .await
        .map_err(|e| format!("Error leyendo respuesta: {}", e))?;

    Ok(response_text)
}

fn main() {
    tauri::Builder::default()
        .invoke_handler(tauri::generate_handler![register_attendance])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
```

---

## 📦 Configuración del Proyecto

### package.json (dependencias)

```json
{
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0"
  },
  "devDependencies": {
    "@tauri-apps/api": "^1.5.0",
    "@tauri-apps/cli": "^1.5.0",
    "typescript": "^5.0.0",
    "vite": "^5.0.0"
  }
}
```

### tauri.conf.json

```json
{
  "build": {
    "distDir": "../dist"
  },
  "tauri": {
    "allowlist": {
      "all": false,
      "http": {
        "all": true,
        "request": true,
        "scope": ["http://localhost:8000/**", "https://your-api.com/**"]
      }
    },
    "windows": [
      {
        "title": "Escáner de Asistencia",
        "width": 1024,
        "height": 768,
        "resizable": true,
        "fullscreen": false
      }
    ]
  }
}
```

---

## 🚀 Comandos de Desarrollo

```bash
# Instalar dependencias
npm install

# Modo desarrollo
npm run tauri dev

# Compilar para producción
npm run tauri build
```

---

## 📱 Flujo de Usuario

1. **Configuración Inicial:**
   - Usuario ingresa URL del API
   - Usuario ingresa ID y nombre de la sala
   - Configuración se guarda en localStorage

2. **Escaneo:**
   - Usuario escanea código de barras/QR del estudiante
   - ID se captura automáticamente
   - Se envía request al API

3. **Feedback:**
   - ✅ Éxito: Mensaje verde + sonido + actualización de stats
   - ❌ Error: Mensaje rojo + sonido + descripción del error

4. **Sincronización:**
   - Estadísticas se actualizan con cada registro
   - Contador de ocupación refleja estado actual

---

## 🔒 Consideraciones de Seguridad

1. **HTTPS en Producción:**
   ```javascript
   const apiUrl = process.env.NODE_ENV === 'production' 
     ? 'https://api.universidad.edu' 
     : 'http://localhost:8000';
   ```

2. **Validación de Input:**
   ```typescript
   const sanitizeStudentId = (id: string): string => {
     return id.replace(/[^0-9-Kk]/g, '').toUpperCase();
   };
   ```

3. **Timeouts:**
   - Request timeout: 10 segundos
   - Retry automático: 3 intentos

4. **Almacenamiento Seguro:**
   - Configuración en localStorage (no sensible)
   - No almacenar credenciales

---

## 📊 Monitoreo y Logs

```typescript
// Logger personalizado
class Logger {
  static log(message: string, data?: any) {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ${message}`, data);
    
    // Enviar a servicio de logging si está configurado
    if (process.env.LOGGING_ENDPOINT) {
      fetch(process.env.LOGGING_ENDPOINT, {
        method: 'POST',
        body: JSON.stringify({ timestamp, message, data }),
      }).catch(console.error);
    }
  }

  static error(message: string, error?: any) {
    const timestamp = new Date().toISOString();
    console.error(`[${timestamp}] ERROR: ${message}`, error);
  }
}

// Uso
Logger.log('Asistencia registrada', { studentId: '12345678', roomId: 'A101' });
```

---

## ✅ Checklist de Implementación

- [ ] Instalar dependencias de Tauri
- [ ] Crear servicio de API
- [ ] Implementar componente de escáner
- [ ] Agregar estilos CSS
- [ ] Configurar allowlist de Tauri
- [ ] Agregar sonidos de feedback
- [ ] Implementar almacenamiento de configuración
- [ ] Probar con escáner físico
- [ ] Compilar para producción
- [ ] Distribuir a dispositivos de escaneo

---

**¡Cliente Tauri listo para integración!** 📱✨
