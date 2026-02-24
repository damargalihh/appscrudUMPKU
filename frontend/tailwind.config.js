import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        '../backend/vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        '../backend/storage/framework/views/*.php',
        '../backend/resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#E53935',
                secondary: '#FB8C00',
                accent: '#FBC02D',
                success: '#4caf50',
                brand: {
                    red: '#E53935',
                    orange: '#FB8C00',
                    yellow: '#FBC02D',
                    green: '#7CB342',
                    white: '#FFFFFF',
                },
                sidebar: {
                    dark: '#1a1a2e',
                    darker: '#16162a',
                    hover: '#2a2a4a',
                    active: '#E53935',
                },
                'text-dark': '#1a1a2e',
                'text-light': '#4a4a6a',
            },
        },
    },

    plugins: [forms],
};
