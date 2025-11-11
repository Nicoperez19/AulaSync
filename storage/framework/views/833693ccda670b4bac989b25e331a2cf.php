<div class="p-6" x-data="{ 
        pagina: 0, 
        totalPaginas: Math.ceil(<?php echo e(count($this->getTodosLosEspacios())); ?> / 13),
        transicionando: false
    }" 
    x-init="
        // Emitir página inicial
        window.dispatchEvent(new CustomEvent('actualizar-pagina', { detail: { pagina: pagina + 1, total: totalPaginas } }));
        
        // Emitir información de feriado
        window.dispatchEvent(new CustomEvent('actualizar-feriado', { 
            detail: { 
                esFeriado: <?php echo e($esFeriado ? 'true' : 'false'); ?>, 
                nombreFeriado: '<?php echo e($nombreFeriado); ?>' 
            } 
        }));
        
        // Actualizar página cada 10 segundos con animación
        setInterval(() => {
            if (totalPaginas > 1) {
                pagina = (pagina + 1) % totalPaginas;
                window.dispatchEvent(new CustomEvent('actualizar-pagina', { detail: { pagina: pagina + 1, total: totalPaginas } }));
            }
        }, 10000)
    ">
    
    <!--[if BLOCK]><![endif]--><?php if(count($pisos) > 0): ?>
    
        <div class="relative w-full bg-gray-100 rounded-lg shadow-sm border border-gray-300 overflow-hidden">
            <!--[if BLOCK]><![endif]--><?php if(count($this->getTodosLosEspacios()) > 0): ?>
                <?php $totalPaginas = ceil(count($this->getTodosLosEspacios()) / 13); ?>
                <table class="w-full table-fixed">
                    <colgroup>
                        <col style="width: 16.66%">
                        <col style="width: 8.33%">
                        <col style="width: 35%">
                        <col style="width: 18%">
                        <col style="width: 12%">
                        <col style="width: 10%">
                    </colgroup>
                    <thead>
                        <tr class="bg-red-600 text-white border-b border-gray-300">
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-clock mr-2"></i>Modulo
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-door-open mr-2"></i>Espacio
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-book mr-2"></i>Clase
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-graduation-cap mr-2"></i>Carrera
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-users mr-2"></i>Capacidad
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                <i class="fas fa-circle-info mr-2"></i>Status
                            </th>
                        </tr>
                    </thead>
                </table>
                <div class="relative">
                    <!--[if BLOCK]><![endif]--><?php for($i = 0; $i < $totalPaginas; $i++): ?>
                        <div x-show="pagina === <?php echo e($i); ?>" 
                             x-transition:enter="transition-opacity ease-in-out duration-500"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity ease-in-out duration-500 absolute inset-0"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="w-full">
                            <table class="w-full table-fixed">
                                <colgroup>
                                    <col style="width: 16.66%">
                                    <col style="width: 8.33%">
                                    <col style="width: 35%">
                                    <col style="width: 18%">
                                    <col style="width: 12%">
                                    <col style="width: 10%">
                                </colgroup>
                                <tbody class="divide-y divide-gray-200">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = array_slice($this->getTodosLosEspacios(), $i * 13, 13); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $espacio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e($index % 2 === 0 ? 'bg-white' : 'bg-gray-50'); ?> hover:bg-gray-100 transition-colors duration-200 h-10 border-b border-gray-200">
                                        <!-- Columna 1: Modulo -->
                                            <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                                <!--[if BLOCK]><![endif]--><?php if(($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']) && !empty($espacio['datos_clase']['modulo_inicio']) && !empty($espacio['datos_clase']['modulo_fin'])): ?>
                                                    <div class="font-medium text-gray-900 text-sm">
                                                        <div class="flex items-center gap-2 text-base font-semibold">
                                                                            
                                                           <?php echo e(preg_replace('/^[A-Z]{2}\./', '', $espacio['datos_clase']['modulo_inicio'])); ?> - <?php echo e(preg_replace('/^[A-Z]{2}\./', '', $espacio['datos_clase']['modulo_fin'])); ?>

                                                          
                                                        </div>
                                                        <div class="text-gray-600">
                                                             
                                                            <?php echo e($espacio['datos_clase']['hora_inicio'] ?? '--:--'); ?> - <?php echo e($espacio['datos_clase']['hora_fin'] ?? '--:--'); ?>

                                                        </div>
                                                    </div>
                                                <?php elseif(!empty($espacio['proxima_clase']) && is_array($espacio['proxima_clase'])): ?>
                                                     <div class="flex items-center gap-2 text-base font-semibold">
                                                                         
                                                           <?php echo e(preg_replace('/^[A-Z]{2}\./', '', $espacio['proxima_clase']['modulo_inicio'] ?? '--')); ?> - <?php echo e(preg_replace('/^[A-Z]{2}\./', '', $espacio['proxima_clase']['modulo_fin'] ?? '--')); ?>

                                                        </div>
                                                        <div class="text-gray-600">
                                                            <?php echo e($espacio['proxima_clase']['hora_inicio'] ?? '--:--'); ?> - <?php echo e($espacio['proxima_clase']['hora_fin'] ?? '--:--'); ?>

                                                        </div>
                                                    </div>
                                                <?php elseif($this->moduloActual && !empty($this->moduloActual['numero'])): ?>
                                                    <!--[if BLOCK]><![endif]--><?php if(($this->moduloActual['tipo'] ?? 'modulo') === 'break'): ?>
                                                        <span class="text-base font-semibold text-gray-600">
                                                          ------
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="flex items-center gap-2 text-base font-semibold">
                                                            
                                                        <!----
                                                            <i class="fas fa-clock"></i>
                                                            <?php echo e($this->moduloActual['numero']); ?>

                                                            Pendiente de aprobacion
                                                            -->
                                                        </span>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                        <!-- Columna 2: Espacio -->
                                        <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            <span class="font-semibold text-blue-700 text-sm"><?php echo e($espacio['id_espacio']); ?></span>
                                        </td>
                                        <!-- Columna 3: Estado -->
                                         <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            <?php
                                                $asignatura = $espacio['datos_clase']['nombre_asignatura'] ?? $espacio['proxima_clase']['nombre_asignatura'] ?? null;
                                            ?>
                                            <!--[if BLOCK]><![endif]--><?php if(($espacio['tiene_reserva_solicitante'] ?? false) && !empty($espacio['datos_solicitante'])): ?>
                                                <span class="font-medium text--700 text-sm">Solicitante: <?php echo e($espacio['datos_solicitante']['nombre'] ?? 'N/A'); ?></span>
                                            <?php elseif(($espacio['tiene_reserva_profesor'] ?? false) && !empty($espacio['datos_profesor']) && !empty($espacio['datos_profesor']['nombre'])): ?>
                                                <span class="font-medium text-gray-700 text-sm">
                                                    <div><?php echo e($espacio['datos_profesor']['nombre_asignatura'] ?? $asignatura ?? 'Sin asignatura'); ?></div>
                                                    <div>Profesor: <?php echo e($espacio['datos_profesor']['nombre'] ?? 'N/A'); ?></div>
                                                </span>
                                            <?php elseif(($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']) && isset($espacio['datos_clase']['profesor']) && !empty($espacio['datos_clase']['profesor']['name'])): ?>
                                                <div class="font-medium text-gray-900 text-sm">
                                                    <div><?php echo e($asignatura ?? 'Sin asignatura'); ?></div>
                                                    <div>Prof: <?php echo e($espacio['datos_clase']['profesor']['name'] ?? 'N/A'); ?></div>
                                                </div>
                                            <?php elseif(!empty($espacio['proxima_clase']) && is_array($espacio['proxima_clase'])): ?>
                                                <div class="font-medium text-gray-700 text-sm">
                                                    <div>Próxima: <?php echo e($asignatura ?? 'Clase programada'); ?></div>
                                                    <div>Prof: <?php echo e($espacio['proxima_clase']['profesor'] ?? '-'); ?></div>
                                                </div>
                                            <?php elseif($asignatura): ?>
                                                <div class="font-medium text-gray-900 text-sm">
                                                    <div><?php echo e($asignatura); ?></div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        
                                        <!-- Columna 4: Carrera -->
                                        <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            <!--[if BLOCK]><![endif]--><?php if(($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']['carrera'])): ?>
                                                <span class="font-medium text-gray-700 text-sm"><?php echo e($espacio['datos_clase']['carrera']); ?></span>
                                            <?php else: ?>
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        
                                        <!-- Columna 5: Capacidad -->
                                        <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            <?php
                                                $capacidadMax = $espacio['capacidad_maxima'] ?? 0;
                                                $puestosDisponibles = $espacio['puestos_disponibles'] ?? 0;
                                                $capacidadUtilizada = max(0, $capacidadMax - $puestosDisponibles);
                                                $porcentaje = $capacidadMax > 0 ? round(($capacidadUtilizada / $capacidadMax) * 100) : 0;
                                                
                                                // Determinar color según ocupación
                                                $colorClase = '';
                                                if ($porcentaje >= 90) {
                                                    $colorClase = 'text-red-600 font-bold';
                                                } elseif ($porcentaje >= 70) {
                                                    $colorClase = 'text-orange-600 font-semibold';
                                                } elseif ($porcentaje >= 50) {
                                                    $colorClase = 'text-yellow-600 font-medium';
                                                } else {
                                                    $colorClase = 'text-green-600';
                                                }
                                            ?>
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($capacidadMax > 0): ?>
                                                <div class="flex flex-col gap-1">
                                                    <div class="<?php echo e($colorClase); ?> text-sm">
                                                        <?php echo e($capacidadUtilizada); ?>/<?php echo e($capacidadMax); ?>

                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="h-2 rounded-full <?php echo e($porcentaje >= 90 ? 'bg-red-600' : ($porcentaje >= 70 ? 'bg-orange-500' : ($porcentaje >= 50 ? 'bg-yellow-500' : 'bg-green-500'))); ?>" 
                                                             style="width: <?php echo e($porcentaje); ?>%"></div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-sm">N/A</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        
                                        <!-- Columna 6: Status -->
                                      <td class="px-3 py-1 text-sm align-middle">
                                            <span class="w-4 h-4 rounded-full <?php echo e($this->getEstadoColor($espacio['estado'], $espacio['tiene_clase'] ?? false, $espacio['tiene_reserva_solicitante'] ?? false, $espacio['tiene_reserva_profesor'] ?? false)); ?> flex-shrink-0 inline-block mr-2"></span>
                                            <span class="font-medium text-gray-900 text-sm"><?php echo e($espacio['estado']); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        </div>
                    <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Leyenda de colores -->
    
    </div>

    <script>
        // Actualizar datos cada 60 segundos para evitar sobrecarga del servidor
        setInterval(function() {
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').actualizarAutomaticamente();
        }, 60000); // Aumentado a 60 segundos
        
        // Escuchar eventos de Livewire para actualizar el feriado cuando se recarguen los datos
        document.addEventListener('livewire:load', function() {
            Livewire.on('datosActualizados', function() {
                window.dispatchEvent(new CustomEvent('actualizar-feriado', { 
                    detail: { 
                        esFeriado: <?php echo e($esFeriado ? 'true' : 'false'); ?>, 
                        nombreFeriado: '<?php echo e($nombreFeriado); ?>' 
                    } 
                }));
            });
        });
    </script>
</div><?php /**PATH C:\Users\conym\OneDrive\Documentos\GitHub\AulaSync\resources\views/livewire/modulos-actuales-table.blade.php ENDPATH**/ ?>