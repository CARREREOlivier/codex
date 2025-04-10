// tailwind.config.js
module.exports = {
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

    plugins: [require('@tailwindcss/typography')],
}
