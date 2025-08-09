<template>

    <div class="flex flex-col p-10">
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 leading-tight">Синхронизация гугл таблицы</h1>
        <StoreLink @update-data="handleUpdateData"/>
        <SyncDataList :dataList="dataList" @update-data="handleUpdateData" @show-modal="handleShowModal"/>
    </div>

    <Modal :id="id" :action="action" :show="show" :dataItem="dataItem" @action-modal="handleActionModal" @close-modal="handleCloseModal"/>

</template>

<script setup>

import { ref } from "vue";
import SyncDataList from "./SyncDataList.vue";
import StoreLink from "./StoreLink.vue";
import Modal from "./Modal.vue";

let dataList = ref();
let dataItem = ref();

const id = ref(0);
const action = ref('create');
const show = ref(false);

const handleUpdateData = () => {
    axios.get('api/get-data').then((res) => {
        dataList.value = res.data;
    });
}

const handleShowModal = (params) => {

    id.value = params.id;
    action.value = params.action;

    show.value = false;

    if (params.id) {
        axios.get('/api/read/' + params.id).then((res) => {
            dataItem = res.data;
            show.value = true;
        });
    } else {
        dataItem = {
            'id': 0,
            'name': '',
            'serial_passport':'',
            'number_passport':'',
            'status': 'Allowed'
        }
        show.value = true;
    }
}

const handleActionModal = (action, changedItem) => {

    if (action === 'create') {
        axios.post('/api/create', changedItem).then(async () => {
            await handleUpdateData();
            handleCloseModal();
        })
    }

    if (action === 'update') {
        axios.put('/api/update/' + changedItem.id, changedItem).then(async () => {
            await handleUpdateData();
            handleCloseModal();
        })
    }

    if (action === 'delete') {
        axios.delete('/api/delete/' + changedItem.id).then(async () => {
            await handleUpdateData();
            handleCloseModal();
        })
    }
}

const handleCloseModal = () => {
    show.value = false;
}

</script>
