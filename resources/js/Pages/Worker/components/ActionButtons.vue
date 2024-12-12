<script setup>
const props = defineProps({
    currentWorkDay: Object,
    currentPause: Object,
});

const emits = defineEmits(['onStartWork', 'onStartPause', 'onEndPause', 'onEndWork']);
</script>

<template>
    <button
        v-if="!currentWorkDay"
        @click="$emit('onStartWork')"
        class="bg-green-500 hover:bg-green-700 px-2 py-1 rounded mt-4 lg:mt-0"
    >
        Начать рабочий день
    </button>
<!--    <button-->
<!--        v-else-if="currentWorkDay.end_time === null && !currentPause"-->
<!--        @click="$emit('onStartPause')"-->
<!--        class="bg-yellow-500 text-white px-4 py-2 rounded mr-2"-->
<!--    >-->
<!--        Начать перерыв-->
<!--    </button>-->
<!--    <button-->
<!--        v-else-if="currentPause"-->
<!--        @click="$emit('onEndPause')"-->
<!--        class="bg-blue-500 text-white px-4 py-2 rounded mr-2"-->
<!--    >-->
<!--        Закончить перерыв-->
<!--    </button>-->
    <button
        v-if="currentWorkDay && !currentPause && !currentWorkDay.end_time"
        @click="$emit('onEndWork')"
        class="bg-red-500 hover:bg-red-700 text-white px-2 py-1 rounded mt-4 lg:mt-0"
    >
        Закончить рабочий день
    </button>
</template>

<style scoped>

</style>
