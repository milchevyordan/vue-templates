<script setup lang="ts">
import { ref } from "vue";
import { watch } from "vue";
import { onMounted } from "vue";
import Multiselect from "vue-multiselect";

import "../../css/multiselect.css";

import { Option, Enum } from "@/types";
import { enumToOptions, replaceUnderscores } from "@/utils";

const emit = defineEmits(["select", "remove"]);
const props = withDefaults(
    defineProps<{
        name: string;
        placeholder: string;
        options: Option[] | Enum<any>;
        selectedOptionsValues?: number[];
        reset?: boolean;
        searchable?: boolean;
        disabled?: boolean;
        capitalize?: boolean;
        id?: string;
    }>(),
    {
        multiple: false,
        searchable: true,
        disabled: false,
        capitalize: false,
    }
);

const model = defineModel<null | Array<string | number | null>>();
const optionObjects = ref<Option[]>([]);
const selectedOptionObjects = ref<Option[] | Enum<any>>(null!);

onMounted(() => {
    initOptions();
    initSelectedOptions();
});

watch(
    () => props.options,
    () => {
        initOptions();
    }
);

watch(
    () => props.reset,
    (reset) => {
        if (reset) {
            resetSelect();
        }
    }
);
const select = (option: Option) => {
    model.value = [...(model.value || []), option.value];
    emit("select", {
        name: props.name,
        value: model.value,
    });
};
const remove = (option: Option) => {
    model.value = model.value?.filter((val: string | number | null) => val !== option.value);

    emit("remove", {
        name: props.name,
        value: option,
    });
};
const initOptions = () => {
    optionObjects.value = enumToOptions(
        replaceUnderscores(props.options),
        props.capitalize
    );
};
const initSelectedOptions = () => {
    const selectedValues = props.selectedOptionsValues ?? model.value;
    if (!Array.isArray(selectedValues)) {
        selectedOptionObjects.value = [];
        return;
    }
    selectedOptionObjects.value = optionObjects.value.filter(
        (item) => item.value !== null && selectedValues.includes(item.value)
    );
};
const resetSelect = () => {
    model.value = [];
    selectedOptionObjects.value = [];
};
</script>
<template>
    <Multiselect
        :id="id"
        v-model="selectedOptionObjects"
        :options="optionObjects"
        label="name"
        track-by="value"
        autocomplete="off"
        :multiple="true"
        :searchable="searchable"
        :disabled="disabled"
        :allow-empty="true"
        :placeholder="'Select ' + placeholder"
        :deselect-label="'Press enter to remove'"
        :select-label="'Press enter to select'"
        :selected-label="'Selected'"
        @select="select"
        @remove="remove"
    />
</template>
