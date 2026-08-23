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
        // PRIMARY: Based off #4DC2C1 (Vibrant Teal)
        primary: {
          '50': '#f0fbfb',
          '100': '#d9f4f3',
          '200': '#b7e9e8',
          '300': '#86d8d7',
          '400': '#4dc2c1', // Base color
          '500': '#33a7a7',
          '600': '#298687',
          '700': '#256d6e',
          '800': '#225859',
          '900': '#214a4b',
          '950': '#0e2b2c',
        },
        // SECONDARY: Based off #BAD767 (Lime Green)
        secondary: {
          '50': '#f7f9ee',
          '100': '#ebf1d9',
          '200': '#dae5b6',
          '300': '#c4d48a',
          '400': '#bad767', // Base color
          '500': '#98bc43',
          '600': '#769632',
          '700': '#5a732a',
          '800': '#495c25',
          '900': '#3f4f23',
          '950': '#202b0f',
        },
      },
    },
  },
  plugins: [require('@tailwindcss/typography')],
  darkMode: 'class',
}