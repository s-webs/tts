<script setup>
import axios from 'axios'; // Импорт Axios
import {ref, computed, watch, onUnmounted, handleError} from 'vue';
import {usePage} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    currentWorkDay: Object,
    workDays: Array
})

const daySummary = ref([]);

const fetchDaySummary = async () => {
    try {
        const response = await axios.get('/api/time-entries/day-summary');
        daySummary.value = response.data.actions;
    } catch (error) {
        console.error('Ошибка при загрузке сводки:', error);
    }
};

// Вызов функции при загрузке компонента
fetchDaySummary();

// Определяем, есть ли активный перерыв
const currentPause = computed(() => {
    if (!props.currentWorkDay) return null;
    return props.currentWorkDay.pauses.find((p) => !p.end_time) || null;
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
        await fetchData();
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
        await fetchData();
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
        await fetchDaySummary(); // Обновляем данные
    } catch (error) {
        handleError(error);
    }
};

const activePauseDuration = ref(0); // Хранит продолжительность текущего перерыва в секундах
let intervalId = null; // ID интервала для очистки позже

const startPauseTimer = (pauseStartTime) => {
    clearInterval(intervalId); // Убедитесь, что предыдущий таймер остановлен
    intervalId = setInterval(() => {
        const now = new Date();
        const startTime = new Date(pauseStartTime);
        activePauseDuration.value = Math.floor((now - startTime) / 1000); // Вычисляем разницу в секундах
    }, 1000);
};

const stopPauseTimer = () => {
    clearInterval(intervalId);
    activePauseDuration.value = 0;
};

// Форматирование времени
const formatDuration = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return [hours, minutes, secs]
        .map((val) => String(val).padStart(2, '0'))
        .join(':');
};

// Следим за состоянием активного перерыва
watch(
    () => currentPause.value,
    (pause) => {
        if (pause) {
            startPauseTimer(pause.start_time);
        } else {
            stopPauseTimer();
        }
    },
    { immediate: true }
);

onUnmounted(() => {
    stopPauseTimer(); // Останавливаем таймер при размонтировании компонента
});

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
                <span v-else-if="currentPause" class="text-yellow-500">Вы на перерыве. {{ formatDuration(activePauseDuration) }}</span>
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

            <div class="mt-6">
                <h2 class="text-xl mb-2">Сводка за день</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Тип действия</th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Время</th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Продолжительность</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="action in daySummary" :key="action.time" class="border-t">
                            <td class="py-2 px-4 text-sm text-gray-800">{{ action.type }}</td>
                            <td class="py-2 px-4 text-sm text-gray-800">{{ formatTimestamp(action.time) }}</td>
                            <td class="py-2 px-4 text-sm text-gray-800" v-if="action.duration">{{ action.duration }}</td>
                            <td class="py-2 px-4 text-sm text-gray-800" v-else>-</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
