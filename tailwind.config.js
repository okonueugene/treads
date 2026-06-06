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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary cyan from logo ("THE", "TREAD", chart bars, arrow)
                brand: {
                    50: '#e6f7fe',
                    100: '#b3e8fc',
                    200: '#7dd8f9',
                    300: '#40c8f6',
                    400: '#14b8f0',
                    500: '#00AEEF',
                    600: '#008fc7',
                    700: '#00719f',
                    800: '#005477',
                    900: '#003850',
                },
                // Orange/gold accent from logo chart highlights
                accent: {
                    50: '#fef5e7',
                    100: '#fde4c4',
                    200: '#fbcf97',
                    300: '#f9b96a',
                    400: '#f8a847',
                    500: '#F7941D',
                    600: '#d67d0f',
                    700: '#a8620c',
                    800: '#7c4809',
                    900: '#502f06',
                },
                // Charcoal / black from logo tire tread & background
                surface: {
                    DEFAULT: '#000000',
                    card: '#2D2D2D',
                    muted: '#333333',
                    border: '#4a4a4a',
                    silver: '#A7A9AC',
                },
            },
            boxShadow: {
                card: '0 4px 24px -4px rgba(0, 0, 0, 0.55)',
                glow: '0 0 40px -8px rgba(0, 174, 239, 0.4)',
                'glow-accent': '0 0 32px -8px rgba(247, 148, 29, 0.35)',
            },
            animation: {
                'fade-in': 'fadeIn 0.4s ease-out forwards',
                'slide-up': 'slideUp 0.4s ease-out forwards',
                shimmer: 'shimmer 1.5s infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
            },
        },
    },

    plugins: [forms],
};
