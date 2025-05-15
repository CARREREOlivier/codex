// tailwind.config.js
module.exports = {
    mode: 'jit',
    content: [
        './templates/**/*.html.twig',
        './assets/**/*.js',
        './assets/**/*.css',
    ],
    theme: {
        extend: {
            colors: {
                parchment: '#f9f6ef', // ou #fdfaf4
                ink: '#3b2f2f',
                accent: '#7a5c3e',
            },
            fontFamily: {
                serif: ['"EB Garamond"', 'serif'],
            },
        },
    },

    safelist: [
        { pattern: /bg-(red|orange|purple|blue|green)-[0-9]{3}/ },
        { pattern: /shadow-.*/ },
       ],

    plugins: [require('@tailwindcss/typography')],
}
