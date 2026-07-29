import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary brand scale — matches the Skill Stryx logo's blue → cyan gradient.
                brand: {
                    50: '#eefaff',
                    100: '#d9f2ff',
                    200: '#b8e8ff',
                    300: '#84d8ff',
                    400: '#38c1ff',
                    500: '#0aa2f5',
                    600: '#0079d1',
                    700: '#005fa8',
                    800: '#064e82',
                    900: '#0b3f66',
                    950: '#062a47',
                },
                // Deep navy for sidebars / dark surfaces.
                ink: {
                    50: '#eef1f8',
                    100: '#dbe1ee',
                    200: '#b6c1d9',
                    300: '#8b9ab9',
                    400: '#647096',
                    500: '#4a5677',
                    600: '#374260',
                    700: '#182338',
                    800: '#111a2b',
                    900: '#0a1120',
                    950: '#060b16',
                },
            },
            boxShadow: {
                glow: '0 8px 30px -8px rgba(10, 162, 245, 0.45)',
                card: '0 1px 2px rgba(10, 17, 32, 0.04), 0 8px 24px -8px rgba(10, 17, 32, 0.10)',
            },
            borderRadius: {
                '4xl': '2rem',
            },
        },
    },

    plugins: [forms],
};
