<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { RouteParams } from "ziggy-js";

import { debounce } from "../js/utils";
import { FilterValues } from "../types";

import Modal from "@/Components/Modal.vue";

const props = defineProps<{
    reloadRoute: string;
    reloadRouteParams?: RouteParams<string> | undefined;
    filterValues: FilterValues;
}>();

const showModal = ref<boolean>(false);

const closeModal = () => {
    showModal.value = false;
};

const advancedSearch = debounce(() => {
    const advancedSearchForm = useForm(props.filterValues);

    advancedSearchForm.get(route(props.reloadRoute, props.reloadRouteParams), {
        preserveState: true,
        onSuccess: () => {
            showModal.value = false;
        },
    });
}, 500);
</script>

<template>
    <div>
        <button
            class="w-full border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50 my-2 sm:my-0"
            @click="showModal = true"
        >
            Filters
        </button>

        <Modal
            :show="showModal"
            @close="closeModal"
        >
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                Filters
            </div>

            <slot />

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeModal"
                >
                    Cancel
                </button>

                <button
                    class="bg-[#00A793] text-white px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="advancedSearch()"
                >
                    Search
                </button>
            </div>
        </Modal>
    </div>
</template>
