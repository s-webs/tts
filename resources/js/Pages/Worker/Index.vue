<script setup>
import axios from 'axios'; // Импорт Axios
import {ref, computed, watch, onUnmounted, handleError, onMounted} from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import ActionButtons from "@/Pages/Worker/components/ActionButtons.vue";
import {formatTimestamp, formatDate, formatDuration} from './utils';
import {useI18n} from "vue-i18n";

const {t, locale} = useI18n();

const props = defineProps({
    currentWorkDay: Object,
    workDays: Array
})

const daySummary = ref([]);
const elapsedTime = ref(0); // Время, прошедшее с начала рабочего дня (в секундах)
let timerInterval = null;

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

// Обработка сообщений об ошибках и успехах
const errors = ref([]);
const success = ref('');

const getLocation = async () => {
    return new Promise((resolve, reject) => {
        if (!('geolocation' in navigator)) {
            reject(t('main.notSupportGeolocation'));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                });
            },
            (error) => {
                reject(t('main.errorGetYourLocation'));
            }
        );
    });
};

// Функции для действий
const startWork = async () => {
    try {
        const location = await getLocation(); // Получаем местоположение
        const response = await axios.post('/api/time-entries/start-work', {
            latitude: location.latitude,
            longitude: location.longitude,
        });

        success.value = t('main.' + response.data.message);
        errors.value = [];
        await fetchData();
    } catch (error) {
        if (typeof error === 'string') {
            errors.value = [error]; // Ошибка получения местоположения
        } else if (error.response?.data?.message) {
            errors.value = [t('main.' + error.response.data.message)];
        } else {
            errors.value = [t('main.errorTryAgain')];
        }
        success.value = '';
    }
};

const startElapsedTimeTimer = () => {
    if (props.currentWorkDay?.start_time) {
        const startTime = new Date(props.currentWorkDay.start_time).getTime();
        timerInterval = setInterval(() => {
            const now = Date.now();
            elapsedTime.value = Math.floor((now - startTime) / 1000);
        }, 1000);
    }
};

// Остановка таймера
const stopElapsedTimeTimer = () => {
    clearInterval(timerInterval);
    elapsedTime.value = 0;
};

// Форматированное отображение времени
const formattedElapsedTime = computed(() => {
    const hours = Math.floor(elapsedTime.value / 3600);
    const minutes = Math.floor((elapsedTime.value % 3600) / 60);
    const seconds = elapsedTime.value % 60;
    return [hours, minutes, seconds].map((val) => String(val).padStart(2, '0')).join(':');
});

// Вызов функции для расчета прошедшего времени
watch(() => props.currentWorkDay, (newWorkDay) => {
    if (newWorkDay?.start_time) {
        startElapsedTimeTimer();
    } else {
        stopElapsedTimeTimer();
    }
});

// Функции жизненного цикла
onMounted(() => {
    if (props.currentWorkDay?.start_time) {
        startElapsedTimeTimer();
    }
});

onUnmounted(() => {
    stopElapsedTimeTimer();
});


const endWork = async () => {
    try {
        const location = await getLocation(); // Получаем местоположение
        const response = await axios.post('/api/time-entries/end-work', {
            latitude: location.latitude,
            longitude: location.longitude,
        });

        success.value = t('main.' + response.data.message) || t('main.workingSuccessfullyCompleted');
        errors.value = [];
        await fetchDaySummary(); // Обновляем сводку дня
        await fetchData(); // Обновляем данные рабочего дня
    } catch (error) {
        if (typeof error === 'string') {
            errors.value = [error]; // Ошибка получения местоположения
        } else if (t('main.' + error.response?.data?.message)) {
            errors.value = [t('main.' + error.response.data.message)];
        } else {
            errors.value = [t('main.errorTryAgain')];
        }
        success.value = '';
    }
};


const startPause = async () => {
    try {
        const location = await getLocation(); // Получаем местоположение
        const response = await axios.post('/api/time-entries/start-pause', {
            latitude: location.latitude,
            longitude: location.longitude,
        });

        success.value = response.data.message;
        await fetchDaySummary();
        await fetchData();
    } catch (error) {
        if (typeof error === 'string') {
            errors.value = [error];
        } else if (error.response?.data?.message) {
            errors.value = [t('main.' + error.response.data.message)];
        } else {
            errors.value = [t('main.errorTryAgain')];
        }
        success.value = '';
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
    {immediate: true}
);

onUnmounted(() => {
    stopPauseTimer(); // Останавливаем таймер при размонтировании компонента
});

const endPause = async () => {
    try {
        const location = await getLocation(); // Получаем местоположение
        const response = await axios.post('/api/time-entries/end-pause', {
            latitude: location.latitude,
            longitude: location.longitude,
        });

        success.value = response.data.message || t('main.breakCompletedSuccessfully');
        errors.value = [];
        await fetchDaySummary(); // Обновляем сводку дня
        await fetchData(); // Обновляем данные рабочего дня
    } catch (error) {
        if (typeof error === 'string') {
            errors.value = [error]; // Ошибка получения местоположения
        } else if (error.response?.data?.message) {
            errors.value = [t('main.' + error.response.data.message)];
        } else {
            errors.value = [t('main.errorTryAgain')];
        }
        success.value = '';
    }
};


// Функция для получения обновленных данных
const fetchData = async () => {
    try {
        const response = await axios.get('/time-entries');
        window.location.reload(); // Простое обновление страницы
    } catch (error) {
        console.error('Ошибка при загрузке данных:', error);
    }
};
</script>

<template>
    <AppLayout title="Трекинг">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight text-center">
                {{ t('main.timeTracking') }}
            </h1>
        </template>
        <div class="container mx-auto p-4">
            <div
                class="flex flex-col lg:flex-row justify-between items-center p-4 rounded-lg shadow-[rgba(50,50,93,0.25)_0px_6px_12px_-2px,_rgba(0,0,0,0.3)_0px_3px_7px_-3px] bg-white">
                <div class="">
                    <span v-if="!currentWorkDay" class="text-gray-500">{{ t('main.dontStartedWork') }}.</span>
                    <span v-else-if="currentWorkDay.end_time === null && !currentPause"
                          class="font-bold text-4xl">{{ formattedElapsedTime }}</span>
                    <span v-else-if="currentPause"
                          class="text-yellow-500">{{ t('main.youOnABreak') }}. {{
                            formatDuration(activePauseDuration)
                        }}</span>
                    <span v-else class="text-red-500">{{ t('main.workingDayOver') }}.</span>
                </div>
                <div>
                    <ActionButtons
                        :currentWorkDay="currentWorkDay"
                        :currentPause="currentPause"
                        @onStartWork="startWork"
                        @onEndWork="endWork"
                    />
                </div>
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

            <div
                class="mt-6 p-2 rounded-lg shadow-[rgba(50,50,93,0.25)_0px_6px_12px_-2px,_rgba(0,0,0,0.3)_0px_3px_7px_-3px] bg-white">
                <h2 class="text-xl text-center py-2">{{ t('main.daySummary') }}</h2>
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">{{
                                    t('main.typeAction')
                                }}
                            </th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">{{ t('main.time') }}</th>
                            <!--                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Продолжительность</th>-->
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="action in daySummary" :key="action.time" class="border-t">
                            <td class="py-2 px-4 text-sm text-gray-800">{{ t('main.' + action.type) }}</td>
                            <td class="py-2 px-4 text-sm text-gray-800">{{ formatTimestamp(action.time) }}</td>
                            <!--                            <td class="py-2 px-4 text-sm text-gray-800" v-if="action.duration">{{action.duration}}</td>-->
                            <!--                            <td class="py-2 px-4 text-sm text-gray-800" v-else>-</td>-->
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
