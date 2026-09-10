import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Manrope', 'Inter', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', '"Fira Code"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                amber: {
                    DEFAULT: '#D48A2E',
                    hover: '#C07A22',
                    dark: '#1A1200',
                    tint: '#F8E9D3',
                    'tint-text': '#8A5A10',
                },
                ink: {
                    DEFAULT: '#0E0E11',
                    surface: '#1C1C20',
                    hover: '#252528',
                    text: '#1B1A17',
                    secondary: '#68665D',
                    faint: '#A19E92',
                },
                page: '#EFEEEA',
                card: '#F9F8F5',
                border: '#E1DFD7',
                success: {
                    bg: '#E3EFE2',
                    text: '#2E6E42',
                },
                danger: {
                    bg: '#F6E4E1',
                    text: '#A2412C',
                },
                info: {
                    bg: '#E7ECF6',
                    text: '#3A529C',
                },
                canvas: {
                    base: '#EFEEEA',
                    subtle: '#EFEEEA',
                    elevated: '#F9F8F5',
                },
                glass: {
                    surface: '#F9F8F5',
                    elevated: '#F9F8F5',
                    subtle: '#EFEEEA',
                    border: '#E1DFD7',
                    'border-highlight': '#F9F8F5',
                },
            },
            backdropBlur: {
                xs: '2px',
                glass: '16px',
                'glass-lg': '24px',
                'glass-xl': '36px',
                'glass-2xl': '48px',
            },
            backgroundImage: {
                'liquid-mesh': 'radial-gradient(at 0% 0%, rgba(253, 29, 29, 0.08) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(131, 58, 180, 0.09) 0px, transparent 50%), radial-gradient(at 50% 100%, rgba(252, 176, 69, 0.08) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(6, 182, 212, 0.07) 0px, transparent 50%)',
                'ig-gradient': 'linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%)',
                'ig-vibrant': 'linear-gradient(135deg, #405de6 0%, #833ab4 25%, #c13584 50%, #e1306c 75%, #fd1d1d 100%)',
                'ig-button': 'linear-gradient(135deg, #fd1d1d 0%, #e1306c 50%, #833ab4 100%)',
                'ig-warm': 'linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%)',
                'glass-gradient': 'linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.70) 100%)',
            },
            boxShadow: {
                glass: '0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.02)',
                'glass-lg': '0 12px 32px -4px rgba(0, 0, 0, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.03)',
                'glass-rim': 'inset 0 1px 0 0 rgba(255, 255, 255, 1)',
                'ig-glow': '0 8px 24px -4px rgba(225, 48, 108, 0.35)',
            },
            animation: {
                blob: 'blob 14s infinite',
                'blob-reverse': 'blob-reverse 18s infinite',
                'blob-slow': 'blob 22s infinite',
                shimmer: 'shimmer 2.5s infinite',
                'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                blob: {
                    '0%': { transform: 'translate(0px, 0px) scale(1)' },
                    '33%': { transform: 'translate(35px, -55px) scale(1.15)' },
                    '66%': { transform: 'translate(-25px, 25px) scale(0.92)' },
                    '100%': { transform: 'translate(0px, 0px) scale(1)' },
                },
                'blob-reverse': {
                    '0%': { transform: 'translate(0px, 0px) scale(1)' },
                    '33%': { transform: 'translate(-45px, 45px) scale(1.18)' },
                    '66%': { transform: 'translate(30px, -35px) scale(0.88)' },
                    '100%': { transform: 'translate(0px, 0px) scale(1)' },
                },
                shimmer: {
                    '100%': { transform: 'translateX(100%)' },
                },
            },
        },
    },

    plugins: [forms],
};
