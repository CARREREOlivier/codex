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
                parchment: 'var(--parchmentColor)',
                ink: 'var(--inkColor)',
                accent: 'var(--accentColor)',
                'accent-dark': 'var(--accentDarkColor)',
            },
            fontFamily: {
                serif: ['"EB Garamond"', 'serif'],
            },
        },
    },

    safelist: [
        { pattern: /bg-(red|orange|purple|blue|green)-[0-9]{3}/ },
        { pattern: /shadow-.*/ },
        'video-title',
        'video-homepage',
        'video-section-container',
        'video-content',
        'video-button',
        'video-paragraph',
        'video-overlay-dark'
       ],

    plugins: [require('@tailwindcss/typography')],
}
