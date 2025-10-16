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
            },
            colors: {
                'bloom': {
                    'yellow': {
                        50: '#FEF9E7',
                        100: '#FDF2CE',
                        200: '#FBE59D',
                        300: '#F9D86C',
                        400: '#F7CB3B',
                        500: '#F5A623', // Primary golden yellow from logo
                        600: '#E09518',
                        700: '#B17713',
                        800: '#82590E',
                        900: '#533B09',
                    },
                    'blue': {
                        50: '#E6F7FB',
                        100: '#CCF0F7',
                        200: '#99E0EF',
                        300: '#66D1E7',
                        400: '#33C1DF',
                        500: '#0EA5E9', // Primary bright blue from logo
                        600: '#0B84BA',
                        700: '#08638B',
                        800: '#05425C',
                        900: '#03212E',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
