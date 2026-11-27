import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                display: ['DM Sans', 'Manrope', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                forest: { 50: '#F3F4F2', 100: '#E9ECE8', 600: '#43584C', 700: '#33453B', 800: '#27362E', 900: '#1B2721' },
                saffron: { 100: '#FBE9DE', 400: '#F38A59', 500: '#F05A28', 600: '#D94B1E' },
                oat: { 50: '#FAF9F6', 100: '#F4F2ED', 200: '#E9E6DF', 300: '#D9D4CA' },
                tabarak: { blue: '#24324A', orange: '#F05A28', mist: '#F4F2ED', line: '#E8E5DE', ink: '#191B20' },
            },
            boxShadow: {
                soft: '0 18px 50px rgba(25, 27, 32, 0.08)',
            },
        },
    },

    plugins: [forms],
};
