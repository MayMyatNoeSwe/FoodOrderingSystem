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
            fontFamily: {
                sans: ['"DM Sans"', '"Noto Sans Myanmar"', '"Padauk"', '"Pyidaungsu"', 'system-ui', 'sans-serif'],
                display: ['"Cabinet Grotesk"', '"DM Sans"', '"Noto Sans Myanmar"', '"Padauk"', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};
