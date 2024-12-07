<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { ref, watchEffect } from "vue";

import { isNotEmpty } from "@/utils";

interface FlashMessages {
    errors?: any;
    error?: string;
    success?: string;
}

interface PageProps {
    flash?: FlashMessages;
}

const page = usePage() as { props: PageProps };
const showAlert = ref(false);

const clearFlashMessages = () => {
    usePage().props.flash = null!;
};

const closeAlert = () => {
    showAlert.value = false;
    clearFlashMessages();
};

watchEffect(() => {
    if (
        isNotEmpty(page.props.flash?.errors) ||
        page.props.flash?.error ||
        page.props.flash?.success
    ) {
        showAlert.value = true;

        setTimeout(() => {
            closeAlert();
        }, 1000);
    }
});
</script>

<template>
    <transition name="slide-fade">
        <div
            v-if="showAlert"
            class="fixed inset-0 z-50"
            role="alert"
            @click="closeAlert"
        >
            <div
                v-if="isNotEmpty(page.props.flash?.errors)"
                class="'text-red-800 bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200 py-4 px-10 text-sm rounded-lg cursor-pointer text-center fixed top-6 left-1/2 transform -translate-x-1/2 z-[80]'"
            >
                <div
                    v-for="(error, index) in page.props.flash?.errors"
                    :key="index"
                >
                    {{ error[0] ?? error }}
                </div>
            </div>

            <div
                v-else-if="page.props.flash?.error"
                class="'text-red-800 bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200 py-4 px-10 text-sm rounded-lg cursor-pointer text-center fixed top-6 left-1/2 transform -translate-x-1/2 z-[80]'"
            >
                {{ page.props.flash?.error }}
            </div>

            <div
                v-else-if="page.props.flash?.success"
                class="'text-green-800 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200 py-4 px-10 text-sm rounded-lg cursor-pointer text-center fixed top-6 left-1/2 transform -translate-x-1/2 z-[80]'"
            >
                {{ page.props.flash?.success }}
            </div>
        </div>
    </transition>
</template>
