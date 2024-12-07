<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

import IconChevronDoubleLeft from "../Icons/ChevronDoubleLeft.vue";
import IconChevronLeft from "../Icons/ChevronLeft.vue";
import IconChevronRight from "../Icons/ChevronRight.vue";
import { Paginator } from "../types";

const props = withDefaults(
    defineProps<{
        paginator: Paginator;
        propName?: string;
        perPageOptions?: number[];
    }>(),
    { propName: "dataTable" }
);

const selectedPerPageOption = ref<null | number>(null);

const generateUrlFromCurrent = (
    paramKey: string,
    paramValue: number | string
): string => {
    const url = new URL(window.location.href);
    const searchParams = url.searchParams;

    searchParams.delete(paramKey);
    searchParams.set(paramKey, String(paramValue));

    return url.toString();
};

const handlePerPageItems = async () => {
    await new Promise((resolve, reject) => {
        router.reload({
            data: {
                perPage: selectedPerPageOption.value,
            },
            only: [props.propName],
            onSuccess: resolve,
            onError: reject,
        });
    });
};
</script>

<template>
    <div
        v-if="Object.keys(paginator.links).length > 1"
        class="flex justify-center sm:justify-between my-2 px-5 items-center"
    >
        <div class="hidden sm:block text-sm text-gray-500 dark:text-gray-300">
            Showing
            <span class="font-semibold">
                {{
                    paginator.currentPage * paginator.perPage -
                    (paginator.perPage - 1)
                }}
            </span>
            to
            <span class="font-semibold">
                {{
                    paginator.currentPage == paginator.lastPage
                        ? paginator.itemsLength
                        : paginator.currentPage * paginator.perPage
                }}
            </span>
            of
            <span class="font-semibold">
                {{ paginator.itemsLength }}
            </span>
            Entries
        </div>

        <div
            v-if="perPageOptions"
            class="flex gap-x-2"
        >
            <select
                v-model="selectedPerPageOption"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                @change="handlePerPageItems"
            >
                <option :value="null">
                    Default
                </option>
                <option
                    v-for="option in perPageOptions"
                    :value="option"
                >
                    {{ option }}
                </option>
            </select>
        </div>

        <div class="flex gap-x-2">
            <Link
                v-if="paginator.currentPage - paginator.pagesRange > 1"
                class="element-center rounded w-9 h-9 leading-4 text-sm transition text-gray-500 hover:text-white bg-white dark:bg-gray-900 dark:text-gray-300 hover:bg-indigo-400 dark:hover:bg-indigo-600"
                :only="[propName, 'paginator']"
                :preserve-state="true"
                :href="generateUrlFromCurrent('page', 1)"
                preserve-scroll
            >
                <IconChevronDoubleLeft />
            </Link>

            <Link
                v-if="paginator.currentPage !== 1"
                class="element-center rounded w-9 h-9 leading-4 text-sm transition text-gray-500 hover:text-white bg-white dark:bg-gray-900 dark:text-gray-300 hover:bg-indigo-400 dark:hover:bg-indigo-600"
                :href="
                    generateUrlFromCurrent('page', paginator.currentPage - 1)
                "
            >
                <IconChevronLeft />
            </Link>

            <div v-for="(link, key) in paginator.links">
                <Link
                    class="element-center rounded w-9 h-9 leading-4 text-sm transition"
                    :only="[propName, 'paginator']"
                    preserve-scroll
                    :preserve-state="true"
                    :href="link"
                    :class="
                        key == paginator.currentPage
                            ? 'bg-indigo-400 dark:bg-indigo-600 text-white'
                            : 'text-gray-500 dark:text-gray-300 hover:text-white bg-white dark:bg-gray-900 hover:bg-indigo-400 dark:hover:bg-indigo-600'
                    "
                >
                    {{ key }}
                </Link>
            </div>

            <div
                v-if="
                    paginator.currentPage + paginator.pagesRange <
                        paginator.lastPage
                "
                class="flex items-center gap-2 ml-2"
            >
                <div class="text-xl tracking-widest mt-2 dark:text-gray-300">
                    ...
                </div>
                <Link
                    class="element-center rounded w-9 h-9 leading-4 text-sm transition text-gray-500 hover:text-white bg-white dark:bg-gray-900 dark:text-gray-300 hover:bg-indigo-400 dark:hover:bg-indigo-600"
                    :only="[propName]"
                    preserve-scroll
                    :preserve-state="true"
                    :href="paginator.lastPageUrl"
                >
                    {{ paginator.lastPage }}
                </Link>
            </div>

            <Link
                v-if="paginator.currentPage < paginator.lastPage"
                class="element-center rounded w-9 h-9 leading-4 text-sm transition text-gray-500 hover:text-white bg-white dark:bg-gray-900 dark:text-gray-300 hover:bg-indigo-400 dark:hover:bg-indigo-600"
                :only="[propName]"
                preserve-scroll
                :preserve-state="true"
                :href="
                    generateUrlFromCurrent('page', paginator.currentPage + 1)
                "
            >
                <IconChevronRight />
            </Link>
        </div>
    </div>
</template>