import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'
import focus from '@alpinejs/focus'
import mask from '@alpinejs/mask'
import PerfectScrollbar from 'perfect-scrollbar'
import 'perfect-scrollbar/css/perfect-scrollbar.css'

// Configuración de Alpine
window.Alpine = Alpine
Alpine.plugin(collapse)
Alpine.plugin(focus)
Alpine.plugin(mask)

// Estado principal de la aplicación
Alpine.data('mainState', () => ({
    isDarkMode: localStorage.getItem('dark') === 'true' || 
                (!localStorage.getItem('dark') && window.matchMedia('(prefers-color-scheme: dark)').matches),
    isSidebarOpen: false,
    isSidebarHovered: false,
    scrollingDown: false,
    scrollingUp: false,
    lastScrollTop: 0,

    init() {
        this.handleWindowResize()
        window.addEventListener('scroll', this.handleScroll.bind(this))
        window.addEventListener('resize', this.handleWindowResize.bind(this))
    },

    toggleTheme() {
        this.isDarkMode = !this.isDarkMode
        localStorage.setItem('dark', this.isDarkMode)
    },

    toggleSidebar() {
        this.isSidebarOpen = !this.isSidebarOpen
    },

    handleSidebarHover(value) {
        if (window.innerWidth < 1024) return
        this.isSidebarHovered = value
    },

    handleWindowResize() {
        if (window.innerWidth < 1024) {
            this.isSidebarOpen = false
        }
    },

    handleScroll() {
        const st = window.pageYOffset || document.documentElement.scrollTop
        this.scrollingDown = st > this.lastScrollTop
        this.scrollingUp = st < this.lastScrollTop
        if (st === 0) {
            this.scrollingDown = false
            this.scrollingUp = false
        }
        this.lastScrollTop = st <= 0 ? 0 : st
    }
}))

// Inicializar Alpine
Alpine.start()

// Configuración de Perfect Scrollbar
document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.ps')
    containers.forEach(container => {
        if (typeof PerfectScrollbar !== 'undefined') {
            new PerfectScrollbar(container, {
                suppressScrollX: true,
                wheelPropagation: false
            })
        }
    })
})

// Optimización de carga de imágenes
document.addEventListener('DOMContentLoaded', () => {
    const images = document.querySelectorAll('img[loading="lazy"]')
    if ('loading' in HTMLImageElement.prototype) {
        images.forEach(img => {
            img.src = img.dataset.src
        })
    } else {
        const script = document.createElement('script')
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js'
        document.body.appendChild(script)
    }
})

// Optimización de eventos
const debounce = (func, wait) => {
    let timeout
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout)
            func(...args)
        }
        clearTimeout(timeout)
        timeout = setTimeout(later, wait)
    }
}

// Aplicar debounce a eventos de búsqueda
document.querySelectorAll('input[type="search"]').forEach(input => {
    input.addEventListener('input', debounce((e) => {
        // Tu lógica de búsqueda aquí
    }, 300))
})
