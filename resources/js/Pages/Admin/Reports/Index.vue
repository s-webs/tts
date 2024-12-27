<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref} from "vue";
import {formatTimestamp, formatDate, formatDuration} from '../../../Pages/Worker/utils.js';
import html2pdf from 'html2pdf.js';
import {useI18n} from "vue-i18n";

const {t, locale} = useI18n();

const props = defineProps({
    usersToday: Array,
})

const tabs = [
    {id: 1, label: 'today', component: "todayReports"},
    {id: 2, label: 'range', component: "weeklyReports"},
    {id: 3, label: 'employee', component: "workerReports"},
];

const getCurrentDate = () => {
    const today = new Date();
    return today.toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

// Пример использования
const currentDate = ref(getCurrentDate());

const pdfContent = ref(null)
const pdfContentRange = ref(null)
const pdfContentEployee = ref(null)
const isPdf = ref(false);

const generatePdf = () => {
    isPdf.value = true; // Устанавливаем флаг
    setTimeout(() => {
        const element = pdfContent.value;
        const options = {
            filename: "report.pdf",
            image: {type: "jpeg", quality: 0.98},
            html2canvas: {scale: 2},
            jsPDF: {unit: "pt", format: "a4", orientation: "portrait"},
        };
        html2pdf()
            .from(element)
            .set(options)
            .save()
            .then(() => {
                isPdf.value = false; // Сбрасываем флаг после генерации PDF
            });
    });
};

const generatePdfRange = () => {
    isPdf.value = true; // Устанавливаем флаг
    setTimeout(() => {
        const element = pdfContentRange.value;
        const options = {
            filename: "report.pdf",
            image: {type: "jpeg", quality: 0.98},
            html2canvas: {scale: 2},
            jsPDF: {unit: "pt", format: "a4", orientation: "portrait"},
        };
        html2pdf()
            .from(element)
            .set(options)
            .save()
            .then(() => {
                isPdf.value = false; // Сбрасываем флаг после генерации PDF
            });
    });
};

const generatePdfEmployee = () => {
    isPdf.value = true; // Устанавливаем флаг
    setTimeout(() => {
        const element = pdfContentEployee.value;
        const options = {
            filename: "report.pdf",
            image: {type: "jpeg", quality: 0.98},
            html2canvas: {scale: 2},
            jsPDF: {unit: "pt", format: "a4", orientation: "portrait"},
        };
        html2pdf()
            .from(element)
            .set(options)
            .save()
            .then(() => {
                isPdf.value = false; // Сбрасываем флаг после генерации PDF
            });
    });
};

const activeTab = ref(tabs[0].id); // Активный таб
const startDate = ref("");
const endDate = ref("");
const weeklyReports = ref([]); // Хранит отчеты за выбранный диапазон
const isLoading = ref(false);

// Обработчик для получения отчетов по диапазону дат
const fetchWeeklyReports = async () => {
    if (!startDate.value || !endDate.value) {
        alert("Пожалуйста, выберите обе даты.");
        return;
    }
    isLoading.value = true;
    try {
        const response = await axios.get("/api/reports", {
            params: {start_date: startDate.value, end_date: endDate.value},
        });
        weeklyReports.value = response.data; // Заполняем отчеты с сервера
    } catch (error) {
        console.error("Ошибка при получении отчетов:", error);
        alert("Не удалось получить отчеты за диапазон дат.");
    } finally {
        isLoading.value = false;
    }
};

const allUsers = ref([]); // Список всех пользователей
const selectedEmployee = ref(""); // Выбранный сотрудник
const employeeReport = ref([]); // Отчеты для выбранного сотрудника

const fetchUsers = async () => {
    try {
        const response = await axios.get("/api/users"); // Убедитесь, что этот маршрут возвращает список пользователей
        allUsers.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке пользователей:", error);
    }
};

// Функция для получения отчета по сотруднику
const fetchEmployeeReport = async () => {
    if (!selectedEmployee.value || !startDate.value || !endDate.value) {
        alert("Пожалуйста, заполните все поля.");
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(`/api/reports/employee`, {
            params: {
                employee_id: selectedEmployee.value,
                start_date: startDate.value,
                end_date: endDate.value,
            },
        });
        employeeReport.value = response.data; // Убедитесь, что сервер возвращает корректный отчет
    } catch (error) {
        console.error("Ошибка при получении отчета по сотруднику:", error);
        alert("Не удалось получить отчет.");
    } finally {
        isLoading.value = false;
    }
};

// Загружаем список пользователей при загрузке компонента
fetchUsers();
</script>

<template>
    <AppLayout title="Отчеты">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight text-center">
                {{ t('main.reports') }}
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
                        <span class="text-lg">{{ t('main.' + tab.label) }}</span>
                    </div>
                </div>
            </div>

            <!-- Контент табов -->
            <div
                class="mt-4 p-4 bg-white rounded-lg shadow-[rgba(50,50,93,0.25)_0px_6px_12px_-2px,_rgba(0,0,0,0.3)_0px_3px_7px_-3px]">
                <div v-if="activeTab === 1">
                    <div ref="pdfContent" class="px-8">
                        <div class="">
                            <div class="border-b border-b-black py-4">
                                <div class="flex justify-center gap-2 items-center">
                                    <img src="/assets/logo/Logo-SP.svg" alt="" class="w-12">
                                    <span
                                        class="leading-4 font-bold text-blue-900 "><span>STUDENTTIK <br/> EMHANA</span></span>
                                </div>
                            </div>
                            <div class="text-end mt-8">
                                {{ t('main.date') }}: {{ currentDate }}
                            </div>
                        </div>
                        <div class="overflow-x-auto mt-4">
                            <table class="table table-xs border w-full">
                                <thead>
                                <tr class="border border-black text-center">
                                    <th class="border-r border-black pb-2">№</th>
                                    <th class="border-r border-black pb-2">{{ t('main.employee') }}</th>
                                    <th class="border-r border-black pb-2">{{ t('main.theBeginningWorkingDay') }}</th>
                                    <th class="pb-1">{{ t('main.endOfWorkingDay') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-for="(report, index) in usersToday"
                                    :key="report.id"
                                    class="border border-black">
                                    <th class="border-r border-black pb-2">{{ index + 1 }}</th>
                                    <td class="border-r border-black pb-2 pl-2">{{ report.name }}</td>
                                    <td class="border-r border-black pb-2 text-center">
                                        <span
                                            class="mr-2">{{
                                                report.work_day_today ? formatTimestamp(report.work_day_today.start_time) : '-'
                                            }}</span>
                                        <a
                                            v-if="!isPdf && report.work_day_today && report.work_day_today.longitude_start && report.work_day_today.latitude_start"
                                            :href="'https://yandex.ru/maps/?pt=' + report.work_day_today.longitude_start + ',' + report.work_day_today.latitude_start + '&z=18&l=map'"
                                            target="_blank"
                                            class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs"
                                        >
                                            {{ t('main.location') }}
                                        </a>
                                    </td>
                                    <td class="text-center pb-2">
                                        <span
                                            class="mr-2">{{
                                                report.work_day_today ? formatTimestamp(report.work_day_today.end_time) : '-'
                                            }}</span>
                                        <a
                                            v-if="!isPdf && report.work_day_today && report.work_day_today.longitude_end && report.work_day_today.latitude_end"
                                            :href="'https://yandex.ru/maps/?pt=' + report.work_day_today.longitude_end + ',' + report.work_day_today.latitude_end + '&z=18&l=map'"
                                            target="_blank"
                                            class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs"
                                        >
                                            {{ t('main.location') }}
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <button @click="generatePdf" class="mt-4 px-4 py-2 bg-green-600 text-white rounded">
                        {{ t('main.downloadPdf') }}
                    </button>
                </div>

                <div v-if="activeTab === 2">
                    <div ref="pdfContentRange" class="px-8">
                        <div class="border-b border-b-black py-4">
                            <div class="flex justify-center gap-2 items-center">
                                <img src="/assets/logo/Logo-SP.svg" alt="" class="w-12">
                                <span
                                    class="leading-4 font-bold text-blue-900 "><span>STUDENTTIK <br/> EMHANA</span></span>
                            </div>
                        </div>
                        <!-- Диапазон выбора дат -->
                        <div v-if="!isPdf" class="flex flex-wrap gap-4 mb-6 mt-6">
                            <div class="flex flex-col">
                                <label for="start-date"
                                       class="text-sm font-semibold text-gray-700">{{ t('main.startDate') }}:</label>
                                <input
                                    id="start-date"
                                    type="date"
                                    v-model="startDate"
                                    class="px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300"
                                />
                            </div>
                            <div class="flex flex-col">
                                <label for="end-date" class="text-sm font-semibold text-gray-700">{{
                                        t('main.endDate')
                                    }}:</label>
                                <input
                                    id="end-date"
                                    type="date"
                                    v-model="endDate"
                                    class="px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300"
                                />
                            </div>
                            <button
                                @click="fetchWeeklyReports"
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md self-end"
                            >
                                {{ t('main.showReports') }}
                            </button>
                        </div>

                        <!-- Отображение таблиц по датам -->
                        <div v-if="weeklyReports.length > 0" class="mt-4">
                            <div v-for="(report, dateIndex) in weeklyReports" :key="dateIndex" class="mb-6">
                                <h2 class="text-lg font-bold mb-2">{{ t('main.date') }}: {{ report.date }}</h2>
                                <table class="table table-xs border w-full">
                                    <thead>
                                    <tr class="border border-black text-center">
                                        <th class="border-r border-black pb-2">№</th>
                                        <th class="border-r border-black pb-2">{{ t('main.employee') }}</th>
                                        <th class="border-r border-black pb-2">{{
                                                t('main.theBeginningWorkingDay')
                                            }}
                                        </th>
                                        <th class="pb-2">{{ t('main.endOfWorkingDay') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr
                                        v-for="(user, userIndex) in report.users"
                                        :key="userIndex"
                                        class="border border-black"
                                    >
                                        <td class="border-r border-black pb-2">{{ userIndex + 1 }}</td>
                                        <td class="border-r border-black pb-2">{{ user.name }}</td>
                                        <td class="border-r border-black pb-2 text-center">
                                            <span
                                                class="mr-2">{{
                                                    user.work_day_today ? formatTimestamp(user.work_day_today.start_time) : '-'
                                                }}</span>
                                            <a
                                                v-if="!isPdf && user.work_day_today && user.work_day_today.longitude_start && user.work_day_today.latitude_start"
                                                :href="'https://yandex.ru/maps/?pt=' + user.work_day_today.longitude_start + ',' + user.work_day_today.latitude_start + '&z=18&l=map'"
                                                target="_blank"
                                                class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs"
                                            >
                                                {{ t('main.location') }}
                                            </a>
                                        </td>
                                        <td class="pb-2 text-center">
                                            <span
                                                class="mr-2">{{
                                                    user.work_day_today ? formatTimestamp(user.work_day_today.end_time) : '-'
                                                }}</span>
                                            <a
                                                v-if="!isPdf && user.work_day_today && user.work_day_today.longitude_end && user.work_day_today.latitude_end"
                                                :href="'https://yandex.ru/maps/?pt=' + user.work_day_today.longitude_end + ',' + user.work_day_today.latitude_end + '&z=18&l=map'"
                                                target="_blank"
                                                class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs"
                                            >
                                                {{ t('main.location') }}
                                            </a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="page-break"></div>
                            </div>
                        </div>

                        <div v-else class="text-center text-gray-500">
                            {{ t('main.reportsForSelected') }}
                        </div>
                    </div>
                    <button @click="generatePdfRange" class="mt-4 px-4 py-2 bg-green-600 text-white rounded">
                        {{ t('main.downloadPdf') }}
                    </button>
                </div>

                <div v-if="activeTab === 3">
                    <div ref="pdfContentEployee" class="px-8">
                        <div class="border-b border-b-black py-4">
                            <div class="flex justify-center gap-2 items-center">
                                <img src="/assets/logo/Logo-SP.svg" alt="" class="w-12">
                                <span
                                    class="leading-4 font-bold text-blue-900 "><span>STUDENTTIK <br/> EMHANA</span></span>
                            </div>
                        </div>
                        <!-- Выбор сотрудника -->
                        <div v-if="!isPdf" class="flex flex-wrap gap-4 mb-6 mt-6">
                            <div class="flex flex-col w-full">
                                <label for="employee" class="text-sm font-semibold text-gray-700">{{
                                        t('main.employee')
                                    }}:</label>
                                <select
                                    id="employee"
                                    v-model="selectedEmployee"
                                    class="px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300"
                                >
                                    <option disabled value="">{{ t('main.selectEmployee') }}</option>
                                    <option v-for="user in allUsers" :key="user.id" :value="user.id">{{
                                            user.name
                                        }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Выбор диапазона дат -->
                        <div v-if="!isPdf" class="flex flex-wrap gap-4">
                            <div class="flex flex-col">
                                <label for="start-date-employee"
                                       class="text-sm font-semibold text-gray-700">{{ t('main.startDate') }}:</label>
                                <input
                                    id="start-date-employee"
                                    type="date"
                                    v-model="startDate"
                                    class="px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300"
                                />
                            </div>
                            <div class="flex flex-col">
                                <label for="end-date-employee"
                                       class="text-sm font-semibold text-gray-700">{{ t('main.endDate') }}:</label>
                                <input
                                    id="end-date-employee"
                                    type="date"
                                    v-model="endDate"
                                    class="px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300"
                                />
                            </div>
                            <button
                                @click="fetchEmployeeReport"
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md self-end"
                            >
                                {{ t('main.showReport') }}
                            </button>
                        </div>

                        <!-- Отображение отчета -->
                        <div v-if="isLoading" class="text-center text-gray-500 mt-8">
                            {{ t('main.loading') }}...
                        </div>
                        <div v-else-if="employeeReport.length > 0" class="overflow-x-auto mt-8">
                            <table class="table table-xs border w-full">
                                <thead>
                                <tr class="border border-black text-center">
                                    <th class="border-r border-black pb-2">{{ t('main.employee') }}</th>
                                    <th class="border-r border-black pb-2">{{ t('main.date') }}</th>
                                    <th class="border-r border-black pb-2">{{ t('main.theBeginningWorkingDay') }}</th>
                                    <th class="border-r border-black pb-2">{{ t('main.endOfWorkingDay') }}</th>
                                    <th class="pb-2">{{ t('main.workingHours') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-for="(day, index) in employeeReport"
                                    :key="index"
                                    class="border border-black text-center"
                                >
                                    <td class="border-r border-black pb-2">{{ day.name }}</td>
                                    <td class="border-r border-black pb-2">{{ day.date }}</td>
                                    <td class="border-r border-black pb-2">
                                        <span
                                            class="mr-2">{{
                                                day.start_time ? formatTimestamp(day.start_time) : '-'
                                            }}</span>
                                        <a
                                            v-if="!isPdf && day.longitude_start && day.latitude_start"
                                            :href="'https://yandex.ru/maps/?pt=' + day.longitude_start + ',' + day.latitude_start + '&z=18&l=map'"
                                            target="_blank"
                                            class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs"
                                        >
                                            {{ t('main.location') }}
                                        </a>
                                    </td>
                                    <td class="border-r border-black pb-2">
                                        <span
                                            class="mr-2">{{
                                                day.end_time ? formatTimestamp(day.end_time) : '-'
                                            }}</span>
                                        <a
                                            v-if="!isPdf && day.longitude_end && day.latitude_end"
                                            :href="'https://yandex.ru/maps/?pt=' + day.longitude_end + ',' + day.latitude_end + '&z=18&l=map'"
                                            target="_blank"
                                            class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs"
                                        >
                                            {{ t('main.location') }}
                                        </a>
                                    </td>
                                    <td class="pb-2">
                                        {{ day.work_duration }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center text-gray-500">
                            {{ t('main.reportsSelectedNotFound') }}
                        </div>
                    </div>
                    <button @click="generatePdfEmployee" class="mt-4 px-4 py-2 bg-green-600 text-white rounded">
                        {{ t('main.downloadPdf') }}
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.page-break {
    page-break-before: always;
}
</style>
