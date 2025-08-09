<template>

    <transition name="fade">
        <div v-if="isModalVisible">
            <div
                @click="onToggle"
                class="fixed top-0 left-0 right-0 bottom-0 bg-black opacity-70 inset-0 z-0 h-svh"
            ></div>
            <div
                class="fixed top-1/3 right-0 left-0 w-full max-w-lg p-3 mx-auto my-auto rounded-xl shadow-lg bg-white"
            >
                <div>

                    <h1 class="text-base font-semibold text-gray-900 mb-5 py-1 border-b-1"
                        v-if="modalAction === 'create'">Create</h1>
                    <h1 class="text-base font-semibold text-gray-900 mb-5 py-1 border-b-1"
                        v-if="modalAction === 'read'">Read ({{id}})</h1>
                    <h1 class="text-base font-semibold text-gray-900 mb-5 py-1 border-b-1"
                        v-if="modalAction === 'update'">Update ({{id}})</h1>
                    <h1 class="text-base font-semibold text-gray-900 mb-5 py-1 border-b-1"
                        v-if="modalAction === 'delete'">Delete ({{id}})</h1>

                    <div class="col-span-full mt-2">
                        <label for="person-name" class="block text-sm/6 font-medium text-gray-900">Name</label>
                        <div class="mt-0">
                            <input type="text" v-model="name" name="person-name" id="person-name"
                                   autocomplete="person-name"
                                   :readonly="isReadonly"
                                   :class="{
                                      'bg-gray-200': isReadonly,
                                      'bg-white': !isReadonly,
                                      'cursor-not-allowed': isReadonly
                                    }"
                                   class="block w-full rounded-md px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"/>
                        </div>
                    </div>

                    <div class="col-span-full mt-2">
                        <label for="serial-passport" class="block text-sm/6 font-medium text-gray-900">Serial
                            passport</label>
                        <div class="mt-0">
                            <input type="text" v-model="serial_passport" name="serial-passport" id="serial-passport"
                                   autocomplete="serial-passport"
                                   :readonly="isReadonly"
                                   :class="{
                                      'bg-gray-200': isReadonly,
                                      'bg-white': !isReadonly,
                                      'cursor-not-allowed': isReadonly
                                    }"
                                   maxlength="4" placeholder="Максимум 4 символов"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   class="block w-full rounded-md px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"/>
                        </div>
                    </div>

                    <div class="col-span-full mt-2">
                        <label for="number-passport" class="block text-sm/6 font-medium text-gray-900">Number
                            Passport</label>
                        <div class="mt-0">
                            <input type="text" v-model="number_passport" name="number-passport" id="number-passport"
                                   autocomplete="number-passport"
                                   :readonly="isReadonly"
                                   :class="{
                                      'bg-gray-200': isReadonly,
                                      'bg-white': !isReadonly,
                                      'cursor-not-allowed': isReadonly
                                    }"
                                   maxlength="6" placeholder="Максимум 6 символов"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   class="block w-full rounded-md px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"/>
                        </div>
                    </div>

                    <div class="col-span-full mt-2">
                        <label for="number-passport" class="block text-sm/6 font-medium text-gray-900">Status</label>
                        <div class="mt-0 relative" v-if="!isReadonly">
                            <select v-model="status"
                                    class="w-full placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded pl-3 pr-8 py-1.5 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-400 shadow-sm focus:shadow-md appearance-none cursor-pointer">
                                <option value="Allowed">Allowed</option>
                                <option value="Prohibited">Prohibited</option>
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2"
                                 stroke="currentColor" class="h-5 w-5 ml-1 absolute top-2 right-2.5 text-slate-700">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/>
                            </svg>
                        </div>
                        <div class="mt-0" v-if="isReadonly">
                            <input type="text" v-model="status" name="status" id="status"
                                   :readonly="isReadonly"
                                   :class="{
                                      'bg-gray-200': isReadonly,
                                      'cursor-not-allowed': isReadonly
                                    }"
                                   class="block w-full rounded-md px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"/>
                        </div>
                    </div>

                    <div class="p-3 mt-2 text-center space-x-4 md:block">
                        <button
                            v-if="modalAction !== 'read'"
                            @click="onSave"
                            class="mb-2 md:mb-0 bg-blue-500 px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-md hover:shadow-lg hover:bg-white-100"
                        >
                            OK
                        </button>
                        <button
                            @click="onToggle"
                            class="mb-2 md:mb-0 bg-gray-500 border-gray-500 px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-md hover:shadow-lg hover:bg-white-600"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>

</template>

<script setup>

import {ref, computed, watch} from 'vue';

let props = defineProps(['id', 'action', 'show', 'dataItem']);
let emit = defineEmits(['actionModal', 'closeModal']);

const isModalVisible = computed(() => props.show);
const modalAction = computed(() => props.action);
const isReadonly = computed(() => props.action === 'read' || props.action === 'delete');

let id = ref();
let name = ref();
let serial_passport = ref();
let number_passport = ref();
let status = ref();

// Следим за изменением props.dataItem
watch(() => props.dataItem, (newValue) => {
    if (newValue) {
        id = props.id;
        name = newValue.name;
        serial_passport = newValue.serial_passport;
        number_passport = newValue.number_passport;
        status = newValue.status;
    }
});

const onSave = () => {
    emit('actionModal', modalAction.value, {
        'id': id ? id : 0,
        'name': name,
        'serial_passport': serial_passport,
        'number_passport': number_passport,
        'status': status,
    })
}

const onToggle = () => {
    emit('closeModal');
}

</script>
