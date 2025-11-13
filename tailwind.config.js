/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class', // Enable dark mode dengan class
  theme: {
    extend: {
      colors: {
        // Color palette dari TailAdmin template
        'gray': {
          50: '#f9fafb',
          100: '#f3f4f6',
          200: '#e5e7eb',
          300: '#d1d5db',
          400: '#9ca3af',
          500: '#6b7280',
          600: '#4b5563',
          700: '#374151',
          800: '#1f2937',
          900: '#111827',
        },
      },
      fontSize: {
        'title-2xl': '72px',
        'title-xl': '60px',
        'title-lg': '48px',
        'title-md': '36px',
        'title-sm': '30px',
      },
      zIndex: {
        '999': '999',
        '9999': '9999',
        '99999': '99999',
        '9998': '9998',
      },
      screens: {
        '2xsm': '375px',
        'xsm': '425px',
        '3xl': '2000px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
