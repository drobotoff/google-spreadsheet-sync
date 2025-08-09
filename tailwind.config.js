/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        // Laravel Blade-шаблоны
        './resources/**/*.blade.php',
        './resources/**/*.{html,js,vue,ts,jsx,tsx}',

        // Vue-компоненты (важно!)
        './resources/js/**/*.{vue,js,ts}',

        // Компоненты в корне или в папке views
        './resources/views/**/*.{blade.php,vue}',

        // Если используешь Inertia или другие фреймворки
        './app/View/Components/**/*.{php,vue}',
        './app/Livewire/**/*.{php,vue}',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}
