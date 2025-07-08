import type { Config } from 'tailwindcss'

const config: Config = {
    content: [
        "./resources//**/*.{js,ts,jsx,tsx}",
        "./public/index.html"
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}

export default config