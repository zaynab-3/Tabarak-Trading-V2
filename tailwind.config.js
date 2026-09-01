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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Georgia', 'Cambria', 'serif'],
            },
            colors: {
                forest: { 50: '#eef5f1', 100: '#dbe9e1', 600: '#1e5b45', 700: '#184b39', 800: '#12382b', 900: '#0b271e' },
                saffron: { 100: '#f5ead5', 400: '#d7a854', 500: '#c9963e', 600: '#ac7930' },
                oat: { 50: '#fbfaf7', 100: '#f5f2eb', 200: '#e9e3d8', 300: '#d8cebe' },
                tabarak: { blue: '#4058E1', orange: '#FF5602', mist: '#F5F7FF', line: '#DDE2FF', ink: '#15182A' },
            },
            boxShadow: {
                soft: '0 14px 40px rgba(18, 56, 43, 0.08)',
            },
        },
    },

    plugins: [forms],
};
