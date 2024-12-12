<script setup>
import {ref, computed} from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";

const props = defineProps({
    users: Array,
});

const searchQuery = ref('');
const filteredUsers = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.users;
    }
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(user =>
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.role.toLowerCase().includes(query)
    );
});

// Редактирование
const isEditModalOpen = ref(false);
const editingUser = ref(null);

const openEditModal = (user) => {
    editingUser.value = {...user}; // Копируем данные пользователя для редактирования
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingUser.value = null;
};

const saveUser = async () => {
    try {
        await axios.put(`/users/${editingUser.value.id}`, editingUser.value);
        alert('Пользователь успешно обновлен');
        closeEditModal();
        location.reload(); // Обновляем страницу (можно оптимизировать)
    } catch (error) {
        console.error(error);
        alert('Ошибка при обновлении пользователя');
    }
};

// Удаление
const isDeleteModalOpen = ref(false);
const userToDelete = ref(null);

const openDeleteModal = (user) => {
    userToDelete.value = user;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    userToDelete.value = null;
};

const deleteUser = async () => {
    try {
        await axios.delete(`/users/${userToDelete.value.id}`);
        alert('Пользователь успешно удален');
        closeDeleteModal();
        location.reload(); // Обновляем страницу (можно оптимизировать)
    } catch (error) {
        console.error(error);
        alert('Ошибка при удалении пользователя');
    }
};

// Добавление нового сотрудника
const isAddModalOpen = ref(false);
const newUser = ref({
    name: '',
    email: '',
    role: 'worker',
    password: '', // Поле для пароля
});

const openAddModal = () => {
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
    newUser.value = {
        name: '',
        email: '',
        role: 'worker',
    };
};

const addUser = async () => {
    try {
        await axios.post('/users', newUser.value); // Отправляем все поля, включая пароль
        alert('Пользователь успешно добавлен');
        closeAddModal();
        location.reload(); // Обновляем страницу (можно оптимизировать)
    } catch (error) {
        console.error(error);
        alert('Ошибка при добавлении пользователя');
    }
};
</script>


<template>
    <AppLayout title="Управление сотрудниками">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight text-center">
                Управление сотрудниками
            </h1>
        </template>

        <div class="container mx-auto p-4">
            <div class="bg-white mt-6 p-4 rounded-lg shadow-[rgba(50,50,93,0.25)_0px_6px_12px_-2px,_rgba(0,0,0,0.3)_0px_3px_7px_-3px]">
                <div class="flex items-center justify-between">
                    <div>Общее количество сотрудников: {{ filteredUsers.length }}</div>
                    <div>
                        <button @click="openAddModal" class="px-2 py-1 bg-green-500 hover:bg-green-700 text-gray-100 rounded-md">
                            Добавить
                        </button>
                    </div>
                </div>
                <div class="w-full bg-white mt-4">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Поиск"
                        class="px-3 py-2 border-0 border-b-2 border-gray-200 focus:outline-none focus:ring-0  w-full"
                    />
                </div>
            </div>

            <div class="mt-4">
                <div
                    v-for="user in filteredUsers"
                    :key="user.id"
                    class="flex items-center justify-between w-full border my-2 p-4 bg-white rounded-lg shadow-[rgba(50,50,93,0.25)_0px_6px_12px_-2px,_rgba(0,0,0,0.3)_0px_3px_7px_-3px]"
                >
                    <div>
                        <div>{{ user.name }}</div>
                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                        <div class="text-xs text-gray-400">{{ user.role }}</div>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <button @click="openEditModal(user)"
                                class="px-2 py-1 bg-blue-500 hover:bg-blue-700 text-gray-100 rounded-md">Редактировать
                        </button>
                        <button @click="openDeleteModal(user)"
                                class="px-2 py-1 bg-red-500 hover:bg-red-700 text-gray-100 rounded-md">Удалить
                        </button>
                    </div>
                </div>
                <div v-if="filteredUsers.length === 0" class="text-center text-gray-500 mt-4">
                    Пользователи не найдены.
                </div>
            </div>
        </div>

        <!-- Модальное окно редактирования -->
        <div v-if="isEditModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded-lg">
                <h2 class="text-xl mb-4">Редактирование пользователя</h2>
                <label class="block mb-2">
                    Имя:
                    <input v-model="editingUser.name" type="text" class="w-full p-2 border rounded"/>
                </label>
                <label class="block mb-2">
                    Email:
                    <input v-model="editingUser.email" type="email" class="w-full p-2 border rounded"/>
                </label>
                <label class="block mb-4">
                    Роль:
                    <select v-model="editingUser.role" class="w-full p-2 border rounded">
                        <option value="admin">Администратор</option>
                        <option value="worker">Работник</option>
                    </select>
                </label>
                <div class="flex justify-end gap-2">
                    <button @click="closeEditModal"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-700 text-white rounded-md">Отмена
                    </button>
                    <button @click="saveUser" class="px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white rounded-md">
                        Сохранить
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно удаления -->
        <div v-if="isDeleteModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded-lg">
                <h2 class="text-xl mb-4">Удалить пользователя?</h2>
                <p>Вы действительно хотите удалить пользователя <strong>{{ userToDelete.name }}</strong>?</p>
                <div class="flex justify-end gap-2 mt-4">
                    <button @click="closeDeleteModal"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-700 text-white rounded-md">Отмена
                    </button>
                    <button @click="deleteUser" class="px-4 py-2 bg-red-500 hover:bg-red-700 text-white rounded-md">
                        Удалить
                    </button>
                </div>
            </div>
        </div>
        <!-- Модальное окно добавления -->
        <div v-if="isAddModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded-lg">
                <h2 class="text-xl mb-4">Добавление нового сотрудника</h2>
                <label class="block mb-2">
                    Имя:
                    <input v-model="newUser.name" type="text" class="w-full p-2 border rounded"/>
                </label>
                <label class="block mb-2">
                    Email:
                    <input v-model="newUser.email" type="email" class="w-full p-2 border rounded"/>
                </label>
                <label class="block mb-2">
                    Пароль:
                    <input v-model="newUser.password" type="password" class="w-full p-2 border rounded"/>
                </label>
                <label class="block mb-4">
                    Роль:
                    <select v-model="newUser.role" class="w-full p-2 border rounded">
                        <option value="admin">Администратор</option>
                        <option value="worker">Работник</option>
                    </select>
                </label>
                <div class="flex justify-end gap-2">
                    <button @click="closeAddModal" class="px-4 py-2 bg-gray-500 hover:bg-gray-700 text-white rounded-md">
                        Отмена
                    </button>
                    <button @click="addUser" class="px-4 py-2 bg-green-500 hover:bg-green-700 text-white rounded-md">
                        Добавить
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
