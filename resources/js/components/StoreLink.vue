<template>

    <div class="flex items-center justify-center mt-20 space-x-4" v-show="!isSyncClickFlag">
        <input type="text" v-model="link" class="w-full p-2 border border-gray-300 rounded" placeholder="Введите ссылку на гугл таблицу" />
        <button @click="openLink" class="bg-blue-500 text-white p-2 rounded">Синхронизировать</button>
        <button @click="clearLink" class="bg-red-500 text-white p-2 rounded">Очистить</button>
    </div>

    <div v-show="isSyncClickFlag" class="mt-10 text-gray-600">
        <a :href="link" target="_blank" class="text-blue-500">{{ link }}</a> - <a @click.prevent="changeLink" class="text-red-500 cursor-pointer text-xs">Изменить</a>
    </div>

    <div v-show="!isSyncClickFlag" class="mt-10 text-gray-600">
        Демо ссылка: <a href="javascript:void(0);" @click="link = demo_link" class="text-blue-500">{{ demo_link }}</a>
    </div>

</template>

<script setup>

import { ref, onMounted } from 'vue';

// Объявляем события, которые компонент может выбрасывать
const emit = defineEmits(['updateData']);

const demo_link = ref('https://docs.google.com/spreadsheets/d/1gJ73rQ7iGuSFVLFoSXZoBT0fLxCl2rbLZNklsq-5UBY/edit'); // Изначально ссылка пустая
let link = ref();
let isSyncClickFlag = ref(false); // Флаг для отслеживания клика по кнопке синхронизации

onMounted(() => {
    axios.get('/api/get-sync-link').then((response) => {
        if (response.data['sync-link']) {
            link.value = response.data['sync-link'];
            isSyncClickFlag.value = true;
            emit('updateData');
        }
    })
})

function changeLink() {
    isSyncClickFlag.value = false; // Сброс флага при изменении ссылки
}

function openLink() {

    let url = '';

    if (link.value) {
        try {
            url = new URL(link.value); // Проверка корректности URL
        } catch (e) {
            alert('Пожалуйста, введите корректную ссылку на гугл таблицу.');
            return;
        }
        // Проверка, что это ссылка на гугл таблицу
        if (url.hostname === 'docs.google.com' && url.pathname.startsWith('/spreadsheets/')) {
            isSyncClickFlag.value = true; // Установка флага при клике на кнопку синхронизации
            axios.post('/api/set-sync-link', {
                "sync-link": link.value
            }).then((response) => {
                console.log('Store link successfully')
            }).then((response) => {
                console.log('Run sync data');
                emit('updateData')
            });
        } else {
            alert('Пожалуйста, введите корректную ссылку на гугл таблицу.');
        }
    } else {
        alert('Пожалуйста, введите ссылку.');
    }
}

function clearLink() {
    axios.post('/api/set-sync-link', {
        "sync-link": ''
    }).then((response) => {
        link.value = ''; // Очистка поля ввода при изменении ссылки
        changeLink();
    });
}

</script>
