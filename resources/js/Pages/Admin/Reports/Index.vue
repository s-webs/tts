<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref} from "vue";
import {formatTimestamp, formatDate, formatDuration} from '../../../Pages/Worker/utils.js';

const props = defineProps({
    todayReports: Array,
})

const tabs = [
    {id: 1, label: "Сегодня", component: "todayReports"},
    {id: 2, label: "Диапазон", component: "weeklyReports"},
];

const activeTab = ref(tabs[0].id); // Активный таб
</script>

<template>
    <AppLayout title="Отчеты">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight text-center">
                Отчеты
            </h1>
        </template>

        <div class="container mx-auto p-4">
            <!-- Таб навигации -->
            <div class="mt-6">
                <div
                    class="flex flex-wrap justify-center bg-white shadow-[rgba(50,50,93,0.25)_0px_6px_12px_-2px,_rgba(0,0,0,0.3)_0px_3px_7px_-3px]">
                    <div
                        v-for="tab in tabs"
                        :key="tab.id"
                        :class="[
                        'flex-1 px-4 py-2 cursor-pointer text-center',
                        activeTab === tab.id
                            ? 'inline-flex items-center justify-center border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                            : 'inline-flex items-center justify-center border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out',
                    ]"
                        @click="activeTab = tab.id"
                    >
                        <span class="text-lg">{{ tab.label }}</span>
                    </div>
                </div>
            </div>

            <!-- Контент табов -->
            <div
                class="mt-4 p-4 bg-white rounded-lg shadow-[rgba(50,50,93,0.25)_0px_6px_12px_-2px,_rgba(0,0,0,0.3)_0px_3px_7px_-3px]">
                <div v-if="activeTab === 1">
                    <!-- Заголовок для таблицы -->
                    <div class="hidden sm:grid sm:grid-cols-3 bg-gray-100 py-2 px-4 font-semibold text-gray-600">
                        <div>Сотрудник</div>
                        <div class="text-center">Начало рабочего дня</div>
                        <div class="text-center">Окончание рабочего дня</div>
                    </div>
                    <!-- Контент: Список для больших экранов, карточки для маленьких -->
                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                        <div
                            v-for="(report, index) in todayReports"
                            :key="report.id"
                            :class="['border-b sm:border-none sm:grid sm:grid-cols-3 p-4 bg-gray-50 rounded-lg sm:rounded-none', index % 2 === 0 ? 'bg-gray-50' : 'bg-white']"
                        >
                            <!-- Сотрудник -->
                            <div class="mb-2 sm:mb-0 flex flex-col items-center justify-center gap-2 sm:block">
                                <div class="sm:hidden font-semibold text-gray-600">Сотрудник:</div>
                                <div class="text-sm text-gray-800 font-medium">
                                    {{ index + 1 }}. {{ report.user.name }}
                                </div>
                            </div>

                            <!-- Начало рабочего дня -->
                            <div class="mb-2 sm:mb-0 flex flex-col items-center justify-center gap-2 sm:block">
                                <div class="sm:hidden font-semibold text-gray-600">Начало рабочего дня:</div>
                                <div class="flex sm:flex-col justify-center gap-4 items-center text-sm text-gray-800">
                                    <div class="text-center">
                                        {{ formatTimestamp(report.start_time) }}
                                    </div>
                                    <a v-if="report.longitude_start && report.latitude_start"
                                        :href="'https://yandex.ru/maps/?pt=' + report.longitude_start + ',' + report.latitude_start + '&z=18&l=map'"
                                        target="_blank"
                                        class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs"
                                    >
                                        Местоположение
                                    </a>
                                </div>
                            </div>

                            <!-- Окончание рабочего дня -->
                            <div class="mb-2 sm:mb-0 flex flex-col items-center justify-center gap-2 sm:block">
                                <div class="sm:hidden font-semibold text-gray-600">Окончание рабочего дня:</div>
                                <div class="flex sm:flex-col justify-center gap-4 items-center text-sm text-gray-800">
                                    <div class="text-center">
                                        {{ formatTimestamp(report.end_time) }}
                                    </div>
                                    <a
                                        v-if="report.longitude_end && report.latitude_end"
                                        :href="'https://yandex.ru/maps/?pt=' + report.longitude_end + ',' + report.latitude_end + '&z=18&l=map'"
                                        target="_blank"
                                        class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs"
                                    >
                                        Местоположение
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 2">
                    <h2 class="text-lg font-bold mb-4">Отчеты за неделю</h2>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>

</style>
