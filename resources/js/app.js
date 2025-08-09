import './bootstrap';

import '../css/app.css'; // ← Добавь эту строку

import { createApp } from 'vue';
import App from './components/App.vue'; // Главный компонент

const app = createApp(App);

app.mount('#app');
