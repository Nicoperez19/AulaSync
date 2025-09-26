<!DOCTYPE html>
<html>
<head>
    <title>Debug Login</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .debug { background: #e8f5e9; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .error { background: #ffebee; color: #c62828; }
        .success { background: #e8f5e9; color: #2e7d32; }
        form { margin: 20px 0; }
        input, button { padding: 10px; margin: 5px 0; width: 100%; max-width: 300px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Debug Login - AulaSync</h1>
        
        <div class="debug">
            <h3>Estado del sistema:</h3>
            <p><strong>Fecha:</strong> {{ date('Y-m-d H:i:s') }}</p>
            <p><strong>Usuario autenticado:</strong> {{ Auth::check() ? 'Sí (' . Auth::user()->run . ')' : 'No' }}</p>
            @if(Auth::check())
                <p><strong>Roles:</strong> {{ Auth::user()->getRoleNames()->implode(', ') }}</p>
                <p><strong>Permisos:</strong> 
                    @if(Auth::user()->hasPermissionTo('dashboard'))
                        ✅ Tiene permiso dashboard
                    @else
                        ❌ No tiene permiso dashboard
                    @endif
                </p>
            @endif
        </div>

        @if(!Auth::check())
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <h3>Formulario de Login:</h3>
            <div>
                <label>RUN:</label>
                <input type="text" name="run" placeholder="12345678" required>
            </div>
            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" placeholder="contraseña" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
        @else
        <div class="success">
            <h3>✅ Usuario autenticado correctamente</h3>
            <p>Ahora deberías ser redirigido automáticamente...</p>
            <a href="/espacios">Ir a Espacios</a> |
            <a href="/dashboard">Ir a Dashboard</a> |
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit">Cerrar Sesión</button>
            </form>
        </div>
        @endif

        @if($errors->any())
        <div class="error">
            <h3>Errores:</h3>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</body>
</html>