module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./node_modules/flowbite/**/*.js",
        "./extensions/**/*.blade.php"
    ],
    darkMode: 'class', // or 'media' or 'class'
    theme: {
        extend: {
            colors: {
                primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"},
                gray: {
                    "50": "#F9FAFB",
                    "100": "#F3F4F6",
                    "200": "#E5E7EB",
                    "300": "#D1D5DB",
                    "400": "#9CA3AF",
                    "500": "#6B7280",
                    "600": "#32324a",
                    "700": "#1d1d2f",
                    "800": "#171725",
                    "900": "#11111d"
                },
                rose: {
                    "50": "#fdf2f8",
                    "100": "#fce7f3",
                    "200": "#fbcfe8",
                    "300": "#f9a8d4",
                    "400": "#f472b6",
                    "500": "#ec4899",
                    "600": "#db2777",
                    "700": "#be185d",
                    "800": "#9d174d",
                    "900": "#831843"
                },
                fuchsia: {
                    "50": "#fdf4ff",
                    "100": "#fae8ff",
                    "200": "#f5d0fe",
                    "300": "#f0abfc",
                    "400": "#e879f9",
                    "500": "#d946ef",
                    "600": "#c026d3",
                    "700": "#a21caf",
                    "800": "#86198f",
                    "900": "#701a75"
                },
                purple: {
                    "50": "#faf5ff",
                    "100": "#f3e8ff",
                    "200": "#e9d5ff",
                    "300": "#d8b4fe",
                    "400": "#c084fc",
                    "500": "#a855f7",
                    "600": "#9333ea",
                    "700": "#7e22ce",
                    "800": "#6b21a8",
                    "900": "#581c87"
                },
            }
        },
        fontFamily: {
            'body': [
                'Inter',
                'ui-sans-serif',
                'system-ui',
                '-apple-system',
                'system-ui',
                'Segoe UI',
                'Roboto',
                'Helvetica Neue',
                'Arial',
                'Noto Sans',
                'sans-serif',
                'Apple Color Emoji',
                'Segoe UI Emoji',
                'Segoe UI Symbol',
                'Noto Color Emoji'
            ],
            'sans': [
                'Inter',
                'ui-sans-serif',
                'system-ui',
                '-apple-system',
                'system-ui',
                'Segoe UI',
                'Roboto',
                'Helvetica Neue',
                'Arial',
                'Noto Sans',
                'sans-serif',
                'Apple Color Emoji',
                'Segoe UI Emoji',
                'Segoe UI Symbol',
                'Noto Color Emoji'
            ]
        }
    },
    plugins: [
        require('flowbite/plugin'),
        require('flowbite-typography')
    ],
}
