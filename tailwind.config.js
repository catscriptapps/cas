/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/**/*.php',
    './resources/js/**/*.js',
    './src/**/*.php',
    './index.php',
  ],
  safelist: [
    // General utility classes that might be dynamically generated or used in the app
    'flex', 'hidden', 'block', 'inline-block', 'grid', 'gap-4', 'p-4', 'm-4',
    // Backgrounds
    'bg-blue-500/10',
    'bg-emerald-500/10',
    'bg-orange-500/10',
    'bg-amber-500/10',
    // Text colors
    'text-blue-500',
    'text-emerald-500',
    'text-orange-500',
    'text-amber-500',
  ],
  theme: {
    extend: {
      fontFamily: {
        'sans': ['Quicksand', 'sans-serif'],
      },
      colors: {
        // PRIMARY: Based off #041A49 (Deep Navy)
        primary: {
          '50': '#f2f5fd',
          '100': '#e1e9fa',
          '200': '#bccef5',
          '300': '#89aaf0',
          '400': '#4f81ee',
          '500': '#1257ed',
          '600': '#0c45c0',
          '700': '#083191',
          '800': '#06266b',
          '900': '#041a49', // Base color
          '950': '#020f2c',
        },
        // SECONDARY: Based off #B79B85 (the logo's border tan)
        secondary: {
          '50': '#f9f7f6',
          '100': '#f0edea',
          '200': '#e3d8cf',
          '300': '#d2c0b2',
          '400': '#b79b85', // Base color
          '500': '#a68368',
          '600': '#8d6d53',
          '700': '#705642',
          '800': '#534031',
          '900': '#3a2c22',
          '950': '#261e17',
        },
      },
    },
  },
  plugins: [require('@tailwindcss/typography')],
  darkMode: 'class',
}