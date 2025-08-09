<template>

    <div class="flex">
        <button class="cursor-pointer my-10 mr-10 w-100 bg-blue-500 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded text-xs" @click="manualUpdate">Обновить</button>
        <button class="cursor-pointer my-10 mr-10 w-100 bg-blue-500 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded text-xs" @click="onCreateItem(0)">Создать запись</button>
        <button class="cursor-pointer my-10 mx-10 w-100 bg-blue-500 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded text-xs" v-if="!isLoadingGenerateData" @click="generateDataInDatabase">Генерация 1000 записей в базу</button>
        <button class="cursor-pointer my-10 mx-10 w-100 bg-blue-500 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded text-xs" v-if="isLoadingGenerateData">
            Loading generate data ...
        </button>
        <button class="cursor-pointer my-10 mx-10 w-100 bg-blue-500 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded text-xs" @click="clearGoogleSpreadSheet">Очистить гугл таблицу</button>
        <button class="cursor-pointer my-10 mx-10 w-100 bg-blue-500 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded text-xs" @click="clearDatabase">Очистить базу данных</button>
    </div>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">ID</th>
                <th scope="col" class="px-6 py-3">Name</th>
                <th scope="col" class="px-6 py-3">Serial passport</th>
                <th scope="col" class="px-6 py-3">Number passport</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Comment</th>
                <th scope="col" class="px-6 py-3"></th>
            </tr>
            </thead>
            <tbody>

            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200"
                v-for="(value, key, index) in dataList" :key="key">
                <td class="px-6 py-1">{{ value.id }}</td>
                <th scope="row" class="px-6 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ value.name }}</th>
                <td class="px-6 py-1">{{ value.serial_passport }}</td>
                <td class="px-6 py-1">{{ value.number_passport }}</td>
                <td :class="['px-6', 'py-1', (value.status === 'Allowed' ? 'text-green-500' : 'text-red-500')]">{{ value.status }}</td>
                <td class="px-6 py-1">{{ value.comment }}</td>
                <td class="px-6 py-1 text-right">
                    <button class="cursor-pointer mr-2 mb-1 bg-blue-500 hover:bg-blue-500 text-white text-xs font-bold py-1 px-4 rounded" @click="onReadItem(value.id)">Прочитать</button>
                    <button class="cursor-pointer mr-2 mb-1 bg-green-500 hover:bg-green-500 text-white text-xs font-bold py-1 px-4 rounded" @click="onUpdateItem(value.id)">Изменить</button>
                    <button class="cursor-pointer bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-4 rounded" @click="onDeleteItem(value.id)">Удалить</button>
                </td>
            </tr>

            <tr v-if="dataList && dataList.length === 0" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200">
                <td colspan="7" class="px-6 py-1 text-blue-500 text-center">Нет записей в базе данных</td>
            </tr>

            </tbody>
            <tfoot v-if="dataList && dataList.length !== 0">
            <tr>
                <th colspan="7" class="text-left py-2 px-6">
                    Count: <span v-if="dataList">{{ dataList.length }}</span>
                </th>
            </tr>
            </tfoot>
        </table>
    </div>

</template>

<script setup>

import { ref } from "vue";

let isLoadingGenerateData = ref(false);
let isSyncData = ref(false);

// Объявляем события, которые компонент может выбрасывать
const emit = defineEmits(['updateData', 'showModal']);
defineProps(['dataList']);

const clearDatabase = () => {
    axios.delete('api/clear-database').then((res) => {
        emit('updateData');
    });
}

const generateDataInDatabase = () => {
    isLoadingGenerateData.value = true
    axios.post('api/generate-data-in-database').then((res) => {
        isLoadingGenerateData.value = false
        emit('updateData');
    });
}

const clearGoogleSpreadSheet = () => {
    axios.post('api/clear-google-spreadsheet').then((res) => {
        emit('updateData');
    });
}

const manualUpdate = () => {
    emit('updateData');
}

// CRUD

const onCreateItem = (id) => {
    emit('showModal', {'id': id.value, 'action': 'create'});
}

const onReadItem = (id) => {
    emit('showModal', {'id': id, 'action': 'read'});
}

const onUpdateItem = (id) => {
    emit('showModal', {'id': id, 'action': 'update'});
}

const onDeleteItem = (id) => {
    emit('showModal', {'id': id, 'action': 'delete'});
}

</script>
