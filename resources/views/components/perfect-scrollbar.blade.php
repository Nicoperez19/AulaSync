@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'ps ' . $class]) }}
    x-data
    x-init="
        if (typeof PerfectScrollbar !== 'undefined') {
            const ps = new PerfectScrollbar($el, {
                wheelSpeed: 2,
                wheelPropagation: false,
                minScrollbarLength: 20
            });
            
            // Limpiar cuando el componente se destruya
            $cleanup = () => {
                if (ps) {
                    ps.destroy();
                }
            }
        }
    "
>
    {{ $slot }}
</div>
