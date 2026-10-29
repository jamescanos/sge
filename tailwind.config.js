import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Tonos 50...950 calculados a partir del color de marca (variables --brand-50 ... --brand-950)
const brandScale = Object.fromEntries(
    [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950].map((shade) => [
        shade,
        `rgb(var(--brand-${shade}) / <alpha-value>)`,
    ]),
);

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Color de marca: lo define la identidad corporativa (variables CSS en los layouts).
            // Permite usar bg-brand, text-brand, bg-brand/10, hover:bg-brand-dark, etc.
            colors: {
                brand: {
                    DEFAULT: 'rgb(var(--brand) / <alpha-value>)',
                    dark: 'rgb(var(--brand-dark) / <alpha-value>)',
                    light: 'rgb(var(--brand-light) / <alpha-value>)',
                },
                // Las vistas de las clases anteriores usan «teal»: ahora teal sigue el color de marca
                teal: brandScale,
            },
        },
    },

    plugins: [forms],
};
