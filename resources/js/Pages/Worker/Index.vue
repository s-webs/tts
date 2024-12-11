<script setup>
import axios from 'axios'; // Импорт Axios
import {ref, computed} from 'vue';
import {usePage} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    currentWorkDay: Object,
    workDays: Array
})

// Определяем, есть ли активный перерыв
const currentPause = computed(() => {
    if (!props.currentWorkDay) return null;
    return props.currentWorkDay.pauses.find(p => !p.end_time) || null;
});

// Форматирование даты и времени
const formatTimestamp = (timestamp) => {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleString();
};

const formatDate = (timestamp) => {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleDateString();
};

// Обработка сообщений об ошибках и успехах
const errors = ref([]);
const success = ref('');

// Функции для действий
const startWork = async () => {
    try {
        const response = await axios.post('/api/time-entries/start-work');
        success.value = response.data.message;
        errors.value = [];
        // Обновление данных после успешного действия
        fetchData();
    } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
            errors.value = [error.response.data.message];
        } else {
            errors.value = ['Произошла ошибка. Попробуйте снова.'];
        }
        success.value = '';
    }
};

const endWork = async () => {
    try {
        const response = await axios.post('/api/time-entries/end-work');
        success.value = response.data.message;
        errors.value = [];
        fetchData();
    } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
            errors.value = [error.response.data.message];
        } else {
            errors.value = ['Произошла ошибка. Попробуйте снова.'];
        }
        success.value = '';
    }
};

const startPause = async () => {
    try {
        const response = await axios.post('/api/time-entries/start-pause');
        success.value = response.data.message;
        errors.value = [];
        fetchData();
    } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
            errors.value = [error.response.data.message];
        } else {
            errors.value = ['Произошла ошибка. Попробуйте снова.'];
        }
        success.value = '';
    }
};

const endPause = async () => {
    try {
        const response = await axios.post('/api/time-entries/end-pause');
        success.value = response.data.message;
        errors.value = [];
        fetchData();
    } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
            errors.value = [error.response.data.message];
        } else {
            errors.value = ['Произошла ошибка. Попробуйте снова.'];
        }
        success.value = '';
    }
};

// Функция для получения обновленных данных
const fetchData = async () => {
    try {
        const response = await axios.get('/time-entries');
        // Предполагается, что вы обновите текущий рабочий день и историю рабочих дней
        // Здесь можно использовать reactive свойства или другие подходы для обновления данных
        // Например:
        window.location.reload(); // Простое обновление страницы
    } catch (error) {
        console.error('Ошибка при загрузке данных:', error);
    }
};
</script>

<template>
    <AppLayout>
        <div class="container mx-auto p-4">
            <h1 class="text-2xl mb-4">Тайм-трекинг</h1>

            <!-- Отображение текущего статуса -->
            <div class="mb-4">
                <span v-if="!currentWorkDay" class="text-gray-500">Вы еще не начали рабочий день.</span>
                <span v-else-if="currentWorkDay.end_time === null && !currentPause" class="text-green-500">Рабочий день начат.</span>
                <span v-else-if="currentPause" class="text-yellow-500">Вы на перерыве.</span>
                <span v-else class="text-red-500">Рабочий день завершен.</span>
            </div>

            <!-- Кнопки действий -->
            <div class="mb-6">
                <button
                    v-if="!currentWorkDay"
                    @click="startWork"
                    class="bg-green-500 px-4 py-2 rounded mr-2"
                >
                    Начать рабочий день
                </button>
                <button
                    v-else-if="currentWorkDay.end_time === null && !currentPause"
                    @click="startPause"
                    class="bg-yellow-500 text-white px-4 py-2 rounded mr-2"
                >
                    Начать перерыв
                </button>
                <button
                    v-else-if="currentPause"
                    @click="endPause"
                    class="bg-blue-500 text-white px-4 py-2 rounded mr-2"
                >
                    Закончить перерыв
                </button>
                <button
                    v-if="currentWorkDay && !currentPause"
                    @click="endWork"
                    class="bg-red-500 text-white px-4 py-2 rounded"
                >
                    Закончить рабочий день
                </button>
            </div>

            <!-- Сообщения об ошибках и успехах -->
            <div v-if="errors.length" class="mb-4">
                <div v-for="error in errors" :key="error" class="text-red-500">
                    {{ error }}
                </div>
            </div>

            <div v-if="success" class="mb-4 text-green-500">
                {{ success }}
            </div>

            <!-- Сводка времени -->
            <div class="mb-6">
                <h2 class="text-xl mb-2">Сводка за день</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Дата</th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600 text-end">Время перерывов</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="workDay in workDays" :key="workDay.id" class="border-t">
                            <td class="py-2 px-4 text-sm text-gray-800">{{ formatDate(workDay.start_time) }}</td>
                            <td class="py-2 px-4 text-sm text-gray-800 text-end">{{ workDay.total_pause_time }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- История действий -->
            <div class="mt-6">
                <h2 class="text-xl mb-2">История действий</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Тип действия</th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Время</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="workDay in workDays" :key="workDay.id">
                            <!-- Рабочий день -->
                            <tr class="border-t bg-gray-50">
                                <td colspan="2" class="py-2 px-4 font-semibold text-gray-700">
                                    Рабочий день: {{ formatTimestamp(workDay.start_time) }}
                                </td>
                            </tr>
                            <!-- Перерывы -->
                            <tr v-for="pause in workDay.pauses" :key="pause.id" class="border-t">
                                <td class="py-2 px-4 pl-6 text-sm capitalize text-gray-800">
                                    {{ pause.end_time ? 'Конец перерыва' : 'Начало перерыва' }}
                                </td>
                                <td class="py-2 px-4 text-sm text-gray-800">
                                    {{ formatTimestamp(pause.end_time || pause.start_time) }}
                                </td>
                            </tr>
                            <!-- Завершение рабочего дня -->
                            <tr v-if="workDay.end_time" class="border-t">
                                <td class="py-2 px-4 pl-6 text-sm capitalize text-gray-800">Завершение рабочего дня</td>
                                <td class="py-2 px-4 text-sm text-gray-800">
                                    {{ formatTimestamp(workDay.end_time) }}
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
