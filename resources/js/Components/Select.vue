<script setup lang="ts">
import { ref } from "vue";
import { watch } from "vue";
import { onMounted } from "vue";
import Multiselect from "vue-multiselect";

import "../../css/multiselect.css";

import { Option, Enum } from "@/types";
import { enumToOptions, replaceUnderscores } from "@/utils";

const emit = defineEmits(["select", "remove", "refresh"]);

const props = withDefaults(
    defineProps<{
        name: string;
        placeholder: string;
        options: Option[] | Enum<any>;
        selectedOptionValue?: number;
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

const model = defineModel<null | string | number>();

const optionObjects = ref<Option[]>([]);

const selectedOptionObject = ref<Option[] | Enum<any>>(null!);

onMounted(() => {
    initOptions(props.options);
    initSelectedOption();
});

watch(
    () => props.options,
    (options) => {
        initOptions(options);
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

const refresh = () => {
    initOptions(props.options);
    initSelectedOption();
};

const select = (option: Option) => {
    model.value = option.value;

    emit("select", {
        name: props.name,
        value: option.value,
    });
};

const remove = () => {
    model.value = null;

    emit("remove", {
        name: props.name,
        value: null,
    });
};

const initOptions = (options: Option[] | Enum<any>) => {
    optionObjects.value = enumToOptions(
        replaceUnderscores(options),
        props.capitalize
    );
};

const initSelectedOption = () => {
    selectedOptionObject.value =
        optionObjects.value.find(
            (item) => item.value == (props.selectedOptionValue ?? model.value)
        ) || null;
};

const resetSelect = () => {
    model.value = null;
    selectedOptionObject.value = null;
};
</script>

<template>
    <Multiselect
        v-model="selectedOptionObject"
        :options="optionObjects"
        label="name"
        track-by="value"
        autocomplete="off"
        :multiple="false"
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
