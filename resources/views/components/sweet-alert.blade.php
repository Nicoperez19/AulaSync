@props(['type' => 'success', 'title' => '', 'message' => ''])

<!-- SweetAlert2 Local (sin dependencia externa CDN para intranets aisladas) -->
<script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    

<div data-page-config="SweetAlertConfig" data-config="{{ json_encode(['loginUrl' => route('login'), 'success' => session('success'), 'error' => session('error'), 'warning' => session('warning'), 'info' => session('info'), 'errorsTitle' => request()->routeIs('login') ? '¡Error de Inicio de Sesión!' : '¡Error de Validación!', 'errors' => ($errors->any() && !request()->routeIs('data.index')) ? collect($errors->all())->map(fn ($e) => $e === 'auth.failed' ? 'El usuario o la contraseña ingresada son incorrectos.' : $e)->values() : []]) }}" class="hidden"></div>
