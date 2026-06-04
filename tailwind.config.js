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
                sans: ['Hanken Grotesk', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                primary: {
                    DEFAULT: '#0059bb',
                    dark:    '#004493',
                    light:   '#0070ea',
                },
                secondary: {
                    DEFAULT: '#006e2a',
                    dark:    '#00531e',
                    light:   '#5cfd80',
                },
                tertiary: {
                    DEFAULT: '#00657b',
                    light:   '#007f9b',
                },
                surface: {
                    DEFAULT:   '#f9f9fc',
                    dim:       '#dadadc',
                    low:       '#f3f3f6',
                    container: '#eeeef0',
                    high:      '#e8e8ea',
                    highest:   '#e2e2e5',
                },
                'on-surface': {
                    DEFAULT: '#1a1c1e',
                    variant: '#414754',
                },
                outline: {
                    DEFAULT: '#717786',
                    variant: '#c1c6d7',
                },
            },
        },
    },

    plugins: [forms],
};
