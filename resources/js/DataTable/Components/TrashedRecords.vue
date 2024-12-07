<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

import RadioButtonToggle from "./RadioButtonToggle.vue";
import { RadioToggleInput, ThisRoute } from "../types";

const props = defineProps<{
    propName: string;
}>();

const thisRoute = route() as ThisRoute;

const archivedOptionSelected = ref<boolean>(
    thisRoute.params.filter?.trashed == "true"
);

const handleShowTrashedRecords = async (input: RadioToggleInput) => {
    await new Promise((resolve, reject) => {
        router.reload({
            data: {
                filter: {
                    trashed: input.value,
                },
            },
            only: [props.propName],
            onSuccess: resolve,
            onError: reject,
        });
    });
};
</script>

<template>
    <RadioButtonToggle
        key="filter[trashed]"
        v-model="archivedOptionSelected"
        name="filter[trashed]"
        :left-button-label="'Archived'"
        :right-button-label="'Not Archived'"
        @change="handleShowTrashedRecords"
    />
</template>
