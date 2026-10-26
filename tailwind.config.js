/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './resources/**/*.ts',
  ],
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true,
  },
  theme: {
    extend: {
      colors: {
        // Paleta Oficial Unificada AulaSync
        brand: {
          DEFAULT: '#D12420',
          primary: '#D12420',
          dark: '#950101',
          soft: '#EDB9B9',
          50: '#FDF2F2',
          100: '#FDE8E8',
          200: '#FBD5D5',
          300: '#F8B4B4',
          400: '#EDB9B9',
          500: '#D12420',
          600: '#B81E1A',
          700: '#950101',
          800: '#7B0101',
          900: '#5A0101',
        },
        neutral: {
          canvas: '#F1EFF2',
          border: '#C0C0C0',
          medium: '#8D8D8D',
          dark: '#191919',
          surface: '#FFFFFF',
        },
        action: {
          success: '#10B981',
          'success-hover': '#059669',
          'success-soft': '#D1FAE5',
        },
        primary: {
          50: '#FDF2F2',
          100: '#FDE8E8',
          200: '#FBD5D5',
          300: '#F8B4B4',
          400: '#EDB9B9',
          500: '#D12420',
          600: '#D12420',
          700: '#950101',
          800: '#7B0101',
          900: '#5A0101',
        },
        secondary: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#8D8D8D',
          600: '#737373',
          700: '#525252',
          800: '#404040',
          900: '#191919',
        },
        'cloud-light': '#F1EFF2',
        'light-cloud-blue': '#D2091E',
        'blue-cloud': '#950101',
        'green-add': '#10B981',
        'dark-eval-0': '#121212',
        'dark-eval-1': '#191919',
        'dark-eval-2': '#232323',
        'dark-eval-3': '#2C2C2C',
      },
      fontFamily: {
        sans: ['Roboto', 'sans-serif'],
      },
      spacing: {
        128: '32rem',
      },
      keyframes: {
        modalIn: {
          '0%': { opacity: '0', transform: 'scale(0.95) translateY(6px)' },
          '100%': { opacity: '1', transform: 'scale(1) translateY(0)' },
        },
        modalOut: {
          '0%': { opacity: '1', transform: 'scale(1)' },
          '100%': { opacity: '0', transform: 'scale(0.96)' },
        },
        pulseBrand: {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '.6' },
        },
        shakeSubtle: {
          '0%, 100%': { transform: 'translateX(0)' },
          '20%, 60%': { transform: 'translateX(-4px)' },
          '40%, 80%': { transform: 'translateX(4px)' },
        },
      },
      animation: {
        'modal-in': 'modalIn 250ms cubic-bezier(0.16, 1, 0.3, 1) forwards',
        'modal-out': 'modalOut 200ms ease-in forwards',
        'pulse-brand': 'pulseBrand 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'shake-subtle': 'shakeSubtle 350ms ease-in-out',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
  corePlugins: {
    preflight: true,
  },
}
