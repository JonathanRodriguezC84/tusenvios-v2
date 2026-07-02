import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                blue: {
                    50: '#eef5ff',
                    100: '#d9e9ff',
                    200: '#bcd8ff',
                    300: '#8bbcff',
                    400: '#5598ff',
                    500: '#1f73ff',
                    600: '#075bec',
                    700: '#0047d9',
                    800: '#073aa8',
                    900: '#0a317f',
                    950: '#071f4f',
                },
                indigo: {
                    50: '#eef5ff',
                    100: '#d9e9ff',
                    200: '#bcd8ff',
                    300: '#8bbcff',
                    400: '#5598ff',
                    500: '#1f73ff',
                    600: '#075bec',
                    700: '#0047d9',
                    800: '#073aa8',
                    900: '#0a317f',
                    950: '#071f4f',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                '2xs': ['0.65rem', { lineHeight: '0.9rem' }],
                '3xs': ['0.6rem', { lineHeight: '0.8rem' }],
            },
        },
    },

    plugins: [forms],
};

