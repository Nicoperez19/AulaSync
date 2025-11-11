<div class="px-3 flex-shrink-0 lg:hidden">
    <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'button','iconOnly' => true,'variant' => 'secondary','xShow' => '!isSidebarOpen','xOn:click' => 'isSidebarOpen = !isSidebarOpen','srText' => 'Toggle sidebar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','icon-only' => true,'variant' => 'secondary','x-show' => '!isSidebarOpen','x-on:click' => 'isSidebarOpen = !isSidebarOpen','sr-text' => 'Toggle sidebar']); ?>
        <?php if (isset($component)) { $__componentOriginale54f5f864b1b102f78b215380e462c68 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale54f5f864b1b102f78b215380e462c68 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.menu-fold-left','data' => ['xShow' => 'isSidebarOpen','class' => 'w-6 h-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.menu-fold-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'isSidebarOpen','class' => 'w-6 h-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale54f5f864b1b102f78b215380e462c68)): ?>
<?php $attributes = $__attributesOriginale54f5f864b1b102f78b215380e462c68; ?>
<?php unset($__attributesOriginale54f5f864b1b102f78b215380e462c68); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale54f5f864b1b102f78b215380e462c68)): ?>
<?php $component = $__componentOriginale54f5f864b1b102f78b215380e462c68; ?>
<?php unset($__componentOriginale54f5f864b1b102f78b215380e462c68); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal6762f4f3aefcb76ed1c6bf54c111a56e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6762f4f3aefcb76ed1c6bf54c111a56e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.menu-fold-right','data' => ['xShow' => '!isSidebarOpen','class' => 'w-6 h-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.menu-fold-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => '!isSidebarOpen','class' => 'w-6 h-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6762f4f3aefcb76ed1c6bf54c111a56e)): ?>
<?php $attributes = $__attributesOriginal6762f4f3aefcb76ed1c6bf54c111a56e; ?>
<?php unset($__attributesOriginal6762f4f3aefcb76ed1c6bf54c111a56e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6762f4f3aefcb76ed1c6bf54c111a56e)): ?>
<?php $component = $__componentOriginal6762f4f3aefcb76ed1c6bf54c111a56e; ?>
<?php unset($__componentOriginal6762f4f3aefcb76ed1c6bf54c111a56e); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>


</div>
<?php /**PATH D:\Dev\AulaSync\resources\views/components/sidebar/footer.blade.php ENDPATH**/ ?>