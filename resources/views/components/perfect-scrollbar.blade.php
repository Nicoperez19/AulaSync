@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'ps ' . $class]) }}
    x-data
    x-init="
        if (typeof PerfectScrollbar !== 'undefined') {
            const ps = new PerfectScrollbar($el, {
                wheelSpeed: 1,
                wheelPropagation: false,
                minScrollbarLength: 20,
                suppressScrollX: true
            });
            
            // Actualizar el scrollbar cuando el contenido cambie
            const observer = new MutationObserver(() => {
                ps.update();
            });
            
            observer.observe($el, {
                childList: true,
                subtree: true
            });
            
            // Limpiar cuando el componente se destruya
            $cleanup = () => {
                if (ps) {
                    ps.destroy();
                }
                observer.disconnect();
            }
        }
    "
>
    {{ $slot }}
</div>
