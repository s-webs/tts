<script setup>
import QrcodeVue from 'qrcode.vue'
import {Head} from "@inertiajs/vue3";
</script>

<template>
    <Head title="TTS"/>
    <div
        class="h-screen w-screen overflow-hidden bg-gradient-to-r from-cyan-500 to-blue-500 flex flex-col items-center justify-center p-8">
        <div>
            <div class="flex gap-2 items-center bg-white px-8 py-2 rounded-full">
                <img src="/assets/logo/Logo-SP.svg" alt="" class="w-12">
                <span class="leading-4 font-bold text-blue-900">STUDENTTIK <br/> EMHANA <br/><small class="text-[10px]">TTS</small></span>

            </div>
        </div>
        <div class="bg-white w-full sm:w-2/4 flex flex-col items-center justify-center rounded-lg box-border p-8 mt-4">
            <QrcodeVue :value="route('dashboard')" :size="240"/>
        </div>
    </div>
</template>

<style scoped>

</style>
