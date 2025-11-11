<nav aria-label="main" class="flex flex-col gap-4 my-[2rem] pt-2" style="color:white;">
    <!-- Dashboard - Solo Administrador y Supervisor (NO Usuario) -->
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Administrador|Supervisor')): ?>
    <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Dashboard','href' => ''.e(route('dashboard')).'','isActive' => request()->routeIs('dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','href' => ''.e(route('dashboard')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard'))]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.dashboard','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.dashboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf)): ?>
<?php $attributes = $__attributesOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf; ?>
<?php unset($__attributesOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf)): ?>
<?php $component = $__componentOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf; ?>
<?php unset($__componentOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Monitoreo de Espacios - Todos los roles -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('monitoreo de espacios')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Monitoreo de Espacios','href' => ''.e($primerMapa ? route('plano.show', $primerMapa->id_mapa) : '#').'','isActive' => request()->routeIs('plano.show'),'onclick' => ''.e(!$primerMapa ? 'mostrarSweetAlertNoMapas(event)' : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Monitoreo de Espacios','href' => ''.e($primerMapa ? route('plano.show', $primerMapa->id_mapa) : '#').'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('plano.show')),'onclick' => ''.e(!$primerMapa ? 'mostrarSweetAlertNoMapas(event)' : '').'']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal529f05da3443eaef4aa8815e981f7826 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal529f05da3443eaef4aa8815e981f7826 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.location','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.location'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal529f05da3443eaef4aa8815e981f7826)): ?>
<?php $attributes = $__attributesOriginal529f05da3443eaef4aa8815e981f7826; ?>
<?php unset($__attributesOriginal529f05da3443eaef4aa8815e981f7826); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal529f05da3443eaef4aa8815e981f7826)): ?>
<?php $component = $__componentOriginal529f05da3443eaef4aa8815e981f7826; ?>
<?php unset($__componentOriginal529f05da3443eaef4aa8815e981f7826); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Horarios Espacios - Solo Administrador y Supervisor -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('horarios por espacios')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Horarios por Espacios','href' => ''.e($tieneEspacios ? ($sede ? route('espacios.show', $sede->id_sede) : (auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard'))) : '#').'','isActive' => request()->routeIs('espacios.show'),'onclick' => ''.e(!$tieneEspacios ? 'mostrarSweetAlertNoEspacios(event)' : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Horarios por Espacios','href' => ''.e($tieneEspacios ? ($sede ? route('espacios.show', $sede->id_sede) : (auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard'))) : '#').'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('espacios.show')),'onclick' => ''.e(!$tieneEspacios ? 'mostrarSweetAlertNoEspacios(event)' : '').'']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal0656bd305abc6f376ceab88970af3514 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0656bd305abc6f376ceab88970af3514 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.clock','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.clock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0656bd305abc6f376ceab88970af3514)): ?>
<?php $attributes = $__attributesOriginal0656bd305abc6f376ceab88970af3514; ?>
<?php unset($__attributesOriginal0656bd305abc6f376ceab88970af3514); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0656bd305abc6f376ceab88970af3514)): ?>
<?php $component = $__componentOriginal0656bd305abc6f376ceab88970af3514; ?>
<?php unset($__componentOriginal0656bd305abc6f376ceab88970af3514); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Horarios Profesores - Solo Administrador y Supervisor -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('horarios profesores')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Horarios Profesores','href' => ''.e($tieneProfesores ? route('horarios.index') : '#').'','isActive' => request()->routeIs('horarios.index'),'onclick' => ''.e(!$tieneProfesores ? 'mostrarSweetAlertNoProfesores(event)' : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Horarios Profesores','href' => ''.e($tieneProfesores ? route('horarios.index') : '#').'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('horarios.index')),'onclick' => ''.e(!$tieneProfesores ? 'mostrarSweetAlertNoProfesores(event)' : '').'']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.dashboard','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.dashboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf)): ?>
<?php $attributes = $__attributesOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf; ?>
<?php unset($__attributesOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf)): ?>
<?php $component = $__componentOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf; ?>
<?php unset($__componentOriginaldd7efffb9c9f6e09cb77b3f1b8d38adf); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Carga Masiva - Solo Administrador -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de carga de datos')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Carga Masiva','href' => ''.e(route('data.index')).'','isActive' => request()->routeIs('data.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Carga Masiva','href' => ''.e(route('data.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('data.index'))]); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginalbc8233b62f57ff3c73c8c87589be1263 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbc8233b62f57ff3c73c8c87589be1263 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.upload','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.upload'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbc8233b62f57ff3c73c8c87589be1263)): ?>
<?php $attributes = $__attributesOriginalbc8233b62f57ff3c73c8c87589be1263; ?>
<?php unset($__attributesOriginalbc8233b62f57ff3c73c8c87589be1263); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbc8233b62f57ff3c73c8c87589be1263)): ?>
<?php $component = $__componentOriginalbc8233b62f57ff3c73c8c87589be1263; ?>
<?php unset($__componentOriginalbc8233b62f57ff3c73c8c87589be1263); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Tablero Académico - Todos los roles -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('tablero academico')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Tablero Académico','href' => ''.e(route('modulos.actuales')).'','isActive' => request()->routeIs('modulos.actuales')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tablero Académico','href' => ''.e(route('modulos.actuales')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('modulos.actuales'))]); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal487abc4945b733a771f93a6aa7e492f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal487abc4945b733a771f93a6aa7e492f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.table','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal487abc4945b733a771f93a6aa7e492f1)): ?>
<?php $attributes = $__attributesOriginal487abc4945b733a771f93a6aa7e492f1; ?>
<?php unset($__attributesOriginal487abc4945b733a771f93a6aa7e492f1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal487abc4945b733a771f93a6aa7e492f1)): ?>
<?php $component = $__componentOriginal487abc4945b733a771f93a6aa7e492f1; ?>
<?php unset($__componentOriginal487abc4945b733a771f93a6aa7e492f1); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Acciones Rápidas - Solo Administrador y Supervisor -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin panel')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Acciones Rápidas','href' => ''.e(route('quick-actions.index')).'','isActive' => request()->routeIs('quick-actions.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Acciones Rápidas','href' => ''.e(route('quick-actions.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('quick-actions.*'))]); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal8d1071fa69816bac40c941e52dfd167c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d1071fa69816bac40c941e52dfd167c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.config','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.config'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8d1071fa69816bac40c941e52dfd167c)): ?>
<?php $attributes = $__attributesOriginal8d1071fa69816bac40c941e52dfd167c; ?>
<?php unset($__attributesOriginal8d1071fa69816bac40c941e52dfd167c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8d1071fa69816bac40c941e52dfd167c)): ?>
<?php $component = $__componentOriginal8d1071fa69816bac40c941e52dfd167c; ?>
<?php unset($__componentOriginal8d1071fa69816bac40c941e52dfd167c); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Reportería - Solo Administrador y Supervisor -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reportes')): ?>
        <?php if (isset($component)) { $__componentOriginal75798e99d14d1b7520450041da5068d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal75798e99d14d1b7520450041da5068d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.dropdown','data' => ['title' => 'Reportes','active' => Str::startsWith(request()->route()->uri(), 'reportes')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Reportes','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(Str::startsWith(request()->route()->uri(), 'reportes'))]); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal0eb582c370058102933a94667aeb70b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0eb582c370058102933a94667aeb70b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chart-bar','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.chart-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0eb582c370058102933a94667aeb70b4)): ?>
<?php $attributes = $__attributesOriginal0eb582c370058102933a94667aeb70b4; ?>
<?php unset($__attributesOriginal0eb582c370058102933a94667aeb70b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0eb582c370058102933a94667aeb70b4)): ?>
<?php $component = $__componentOriginal0eb582c370058102933a94667aeb70b4; ?>
<?php unset($__componentOriginal0eb582c370058102933a94667aeb70b4); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>

            <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Accesos registrados','href' => ''.e(route('reportes.accesos')).'','isActive' => request()->routeIs('reportes.accesos')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Accesos registrados','href' => ''.e(route('reportes.accesos')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('reportes.accesos'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Análisis por espacios','href' => ''.e(route('reportes.espacios')).'','isActive' => request()->routeIs('reportes.espacios')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Análisis por espacios','href' => ''.e(route('reportes.espacios')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('reportes.espacios'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Análisis por tipo de espacio','href' => ''.e(route('reportes.tipo-espacio')).'','isActive' => request()->routeIs('reportes.tipo-espacio')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Análisis por tipo de espacio','href' => ''.e(route('reportes.tipo-espacio')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('reportes.tipo-espacio'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal75798e99d14d1b7520450041da5068d5)): ?>
<?php $attributes = $__attributesOriginal75798e99d14d1b7520450041da5068d5; ?>
<?php unset($__attributesOriginal75798e99d14d1b7520450041da5068d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal75798e99d14d1b7520450041da5068d5)): ?>
<?php $component = $__componentOriginal75798e99d14d1b7520450041da5068d5; ?>
<?php unset($__componentOriginal75798e99d14d1b7520450041da5068d5); ?>
<?php endif; ?>
    <?php endif; ?>
    <!-- Estadísticas Profesores - Solo Administrador y Supervisor -->
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Administrador|Supervisor')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Clases no realizadas','href' => ''.e(route('clases-no-realizadas.index')).'','isActive' => request()->routeIs('clases-no-realizadas.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Clases no realizadas','href' => ''.e(route('clases-no-realizadas.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('clases-no-realizadas.*'))]); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal0eb582c370058102933a94667aeb70b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0eb582c370058102933a94667aeb70b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.chart-bar','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.chart-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0eb582c370058102933a94667aeb70b4)): ?>
<?php $attributes = $__attributesOriginal0eb582c370058102933a94667aeb70b4; ?>
<?php unset($__attributesOriginal0eb582c370058102933a94667aeb70b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0eb582c370058102933a94667aeb70b4)): ?>
<?php $component = $__componentOriginal0eb582c370058102933a94667aeb70b4; ?>
<?php unset($__componentOriginal0eb582c370058102933a94667aeb70b4); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Gestión de Licencias - Protegido por permiso -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar licencias profesores')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Licencias Profesores','href' => ''.e(route('licencias.index')).'','isActive' => request()->routeIs('licencias.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Licencias Profesores','href' => ''.e(route('licencias.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('licencias.*'))]); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <i class="flex-shrink-0 w-6 h-6 fa-solid fa-file-medical" aria-hidden="true"></i>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Recuperación de Clases - Protegido por permiso -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar recuperacion clases')): ?>
        <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['title' => 'Recuperación de Clases','href' => ''.e(route('recuperacion-clases.index')).'','isActive' => request()->routeIs('recuperacion-clases.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recuperación de Clases','href' => ''.e(route('recuperacion-clases.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('recuperacion-clases.*'))]); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <i class="flex-shrink-0 w-6 h-6 fa-solid fa-calendar-check" aria-hidden="true"></i>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
    <?php endif; ?>

    <!-- Mantenedores - Solo Administrador -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['mantenedor de roles', 'mantenedor de permisos', 'mantenedor de universidades', 'mantenedor de facultades', 'mantenedor de areas academicas', 'mantenedor de carreras', 'mantenedor de pisos', 'mantenedor de espacios', 'mantenedor de reservas', 'mantenedor de asignaturas', 'mantenedor de mapas', 'mantenedor de campus', 'mantenedor de sedes', 'mantenedor de profesores', 'mantenedor de visitantes', 'mantenedor de feriados', 'mantenedor de configuracion', 'mantenedor de escuelas', 'mantenedor de jefes de carrera', 'mantenedor de asistentes academicos'])): ?>
        <?php if (isset($component)) { $__componentOriginal75798e99d14d1b7520450041da5068d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal75798e99d14d1b7520450041da5068d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.dropdown','data' => ['title' => 'Mantenedores','active' => Str::startsWith(request()->route()->uri(), 'users')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Mantenedores','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(Str::startsWith(request()->route()->uri(), 'users'))]); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal8d1071fa69816bac40c941e52dfd167c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d1071fa69816bac40c941e52dfd167c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.config','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.config'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8d1071fa69816bac40c941e52dfd167c)): ?>
<?php $attributes = $__attributesOriginal8d1071fa69816bac40c941e52dfd167c; ?>
<?php unset($__attributesOriginal8d1071fa69816bac40c941e52dfd167c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8d1071fa69816bac40c941e52dfd167c)): ?>
<?php $component = $__componentOriginal8d1071fa69816bac40c941e52dfd167c; ?>
<?php unset($__componentOriginal8d1071fa69816bac40c941e52dfd167c); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de areas academicas')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Áreas Académicas','href' => ''.e(route('academic_areas.index')).'','isActive' => request()->routeIs('academic_areas.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Áreas Académicas','href' => ''.e(route('academic_areas.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('academic_areas.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de asignaturas')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Asignaturas','href' => ''.e(route('asignaturas.index')).'','isActive' => request()->routeIs('asignaturas.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Asignaturas','href' => ''.e(route('asignaturas.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('asignaturas.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de asistentes academicos')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Asistentes Académicos','href' => ''.e(route('asistentes-academicos.index')).'','isActive' => request()->routeIs('asistentes-academicos.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Asistentes Académicos','href' => ''.e(route('asistentes-academicos.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('asistentes-academicos.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de campus')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Campus','href' => ''.e(route('campus.index')).'','isActive' => request()->routeIs('campus.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Campus','href' => ''.e(route('campus.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('campus.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de carreras')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Carreras','href' => ''.e(route('careers.index')).'','isActive' => request()->routeIs('careers.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Carreras','href' => ''.e(route('careers.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('careers.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de configuracion')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Configuración','href' => ''.e(route('configuracion.index')).'','isActive' => request()->routeIs('configuracion.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Configuración','href' => ''.e(route('configuracion.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('configuracion.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Administrador')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Correos Masivos','href' => ''.e(route('correos-masivos.index')).'','isActive' => request()->routeIs('correos-masivos.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Correos Masivos','href' => ''.e(route('correos-masivos.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('correos-masivos.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de dias feriados')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Días Feriados','href' => ''.e(route('dias-feriados.index')).'','isActive' => request()->routeIs('dias-feriados.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Días Feriados','href' => ''.e(route('dias-feriados.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dias-feriados.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de escuelas')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Escuelas','href' => ''.e(route('escuelas.index')).'','isActive' => request()->routeIs('escuelas.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Escuelas','href' => ''.e(route('escuelas.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('escuelas.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de espacios')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Espacios','href' => ''.e(route('spaces_index')).'','isActive' => request()->routeIs('spaces_index') || request()->routeIs('spaces.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Espacios','href' => ''.e(route('spaces_index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('spaces_index') || request()->routeIs('spaces.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de facultades')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Facultades','href' => ''.e(route('faculties.index')).'','isActive' => request()->routeIs('faculties.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Facultades','href' => ''.e(route('faculties.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('faculties.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de jefes de carrera')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Jefes de Carrera','href' => ''.e(route('jefes-carrera.index')).'','isActive' => request()->routeIs('jefes-carrera.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Jefes de Carrera','href' => ''.e(route('jefes-carrera.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('jefes-carrera.*'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de mapas')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Mapa','href' => ''.e(route('mapas.index')).'','isActive' => request()->routeIs('maps.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Mapa','href' => ''.e(route('mapas.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('maps.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de permisos')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Permisos','href' => ''.e(route('permissions.index')).'','isActive' => request()->routeIs('permissions.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Permisos','href' => ''.e(route('permissions.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('permissions.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de pisos')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Pisos','href' => ''.e(route('floors_index')).'','isActive' => request()->routeIs('floors_index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pisos','href' => ''.e(route('floors_index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('floors_index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de profesores')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Profesores','href' => ''.e(route('professors.index')).'','isActive' => request()->routeIs('professors.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Profesores','href' => ''.e(route('professors.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('professors.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de visitantes')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Visitantes','href' => ''.e(route('visitantes.index')).'','isActive' => request()->routeIs('visitantes.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Visitantes','href' => ''.e(route('visitantes.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('visitantes.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de roles')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Roles','href' => ''.e(route('roles.index')).'','isActive' => request()->routeIs('roles.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Roles','href' => ''.e(route('roles.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('roles.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de sedes')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Sedes','href' => ''.e(route('sedes.index')).'','isActive' => request()->routeIs('sedes.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Sedes','href' => ''.e(route('sedes.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('sedes.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de universidades')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Universidades','href' => ''.e(route('universities.index')).'','isActive' => request()->routeIs('universities.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Universidades','href' => ''.e(route('universities.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('universities.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mantenedor de usuarios')): ?>
                <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Usuarios','href' => ''.e(route('users.index')).'','isActive' => request()->routeIs('users.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Usuarios','href' => ''.e(route('users.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('users.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal75798e99d14d1b7520450041da5068d5)): ?>
<?php $attributes = $__attributesOriginal75798e99d14d1b7520450041da5068d5; ?>
<?php unset($__attributesOriginal75798e99d14d1b7520450041da5068d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal75798e99d14d1b7520450041da5068d5)): ?>
<?php $component = $__componentOriginal75798e99d14d1b7520450041da5068d5; ?>
<?php unset($__componentOriginal75798e99d14d1b7520450041da5068d5); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Auxiliar')): ?>
    <?php if (isset($component)) { $__componentOriginal75798e99d14d1b7520450041da5068d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal75798e99d14d1b7520450041da5068d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.dropdown','data' => ['title' => 'Visualizador','active' => Str::startsWith(request()->route()->uri(), 'universidades')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Visualizador','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(Str::startsWith(request()->route()->uri(), 'universidades'))]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginale510080392c2b04add0ccfa2599be415 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale510080392c2b04add0ccfa2599be415 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.university','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.university'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale510080392c2b04add0ccfa2599be415)): ?>
<?php $attributes = $__attributesOriginale510080392c2b04add0ccfa2599be415; ?>
<?php unset($__attributesOriginale510080392c2b04add0ccfa2599be415); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale510080392c2b04add0ccfa2599be415)): ?>
<?php $component = $__componentOriginale510080392c2b04add0ccfa2599be415; ?>
<?php unset($__componentOriginale510080392c2b04add0ccfa2599be415); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
        <?php if (isset($component)) { $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.sublink','data' => ['title' => 'Mapa','href' => ''.e(route('mapas.index')).'','isActive' => request()->routeIs('maps.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.sublink'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Mapa','href' => ''.e(route('mapas.index')).'','isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('maps.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $attributes = $__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__attributesOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89)): ?>
<?php $component = $__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89; ?>
<?php unset($__componentOriginal064f6c9edbcdd6f4e7eb9faa53722c89); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal75798e99d14d1b7520450041da5068d5)): ?>
<?php $attributes = $__attributesOriginal75798e99d14d1b7520450041da5068d5; ?>
<?php unset($__attributesOriginal75798e99d14d1b7520450041da5068d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal75798e99d14d1b7520450041da5068d5)): ?>
<?php $component = $__componentOriginal75798e99d14d1b7520450041da5068d5; ?>
<?php unset($__componentOriginal75798e99d14d1b7520450041da5068d5); ?>
<?php endif; ?>
    <?php endif; ?>
</nav>
<?php /**PATH C:\Users\conym\OneDrive\Documentos\GitHub\AulaSync\resources\views/components/sidebar/content.blade.php ENDPATH**/ ?>