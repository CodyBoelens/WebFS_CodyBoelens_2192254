/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                red: {
                    50:  '#fff1f1',
                    100: '#ffe0e0',
                    200: '#ffc6c6',
                    300: '#ff9d9d',
                    400: '#ff6464',
                    500: '#ff3030',
                    600: '#f01111',
                    700: '#c0392b', // Gouden Draak rood
                    800: '#9b1a0d',
                    900: '#7f1b0f',
                    950: '#450a05',
                },
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
