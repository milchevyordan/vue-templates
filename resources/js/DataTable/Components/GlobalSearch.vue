<script setup lang="ts">
import { useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Magnifying from "@/Icons/Magnifying.vue";
import { debounce } from "@/utils";

const props = defineProps<{
    propName: string;
}>();

const filterGlobalValue = new URLSearchParams(window.location.search).get(
    "filter[global]"
);

const globalSearchForm = useForm({
    filter: {
        global: filterGlobalValue,
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    },
});

const inputValue = ref<null | string>(globalSearchForm.filter.global);

const globalSearch = debounce(() => {
    const thisRoute = route();
    const params = { ...thisRoute.params, page: 1 };
    globalSearchForm.filter.global = inputValue.value;

    router.visit(route(thisRoute.current() as string, params), {
        method: "get",
        data: {
            filter: {
                global: globalSearchForm.filter.global,
                timeZone: globalSearchForm.filter.timeZone,
            },
        },
        preserveState: true,
        preserveScroll: true,
        only: [props.propName],
    });
}, 1200);
</script>

<template>
    <div class="md:flex items-center justify-between">
        <div class="md:w-80 lg:w-96 relative">
            <div
                class="absolute inset-y-0 left-0 flex items-center pl-3 z-10 cursor-pointer"
                @click="globalSearch"
            >
                <Magnifying
                    stroke="2"
                    :classes="'w-5 h-5 text-gray-400 dark:text-gray-300'"
                />
            </div>

            <div class="relative">
                <input
                    v-model="inputValue"
                    class="border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-300 pl-10 pr-4 text-sm rounded-md shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 block w-full p-2.5 placeholder-gray-400 dark:placeholder-gray-500 peer transition hover:bg-gray-50 dark:hover:bg-gray-800 focus:bg-gray-50 dark:focus:bg-gray-900 z-0"
                    :placeholder="'Search...'"
                    @input="globalSearch"
                >
            </div>
        </div>
    </div>
</template>
