<script setup lang="ts">
import RadioButton from "./RadioButton.vue";

const emit = defineEmits(["change"]);

const props = withDefaults(
    defineProps<{
        name: string;
        classes?: string;
        disabled?: boolean;
        leftButtonLabel?: string;
        rightButtonLabel?: string;
    }>(),
    {
        classes: "flex sm:justify-end mb-3.5 sm:mb-0",
        leftButtonLabel: "Yes",
        rightButtonLabel: "No",
    }
);

const model = defineModel<boolean>({ default: false });

const handleModelChange = (value: boolean) => {
    model.value = value;

    emit("change", {
        name: props.name,
        value: value,
    });
};
</script>

<template>
    <div>
        <div :class="classes">
            <div>
                <RadioButton
                    :id="`yes_` + name"
                    :label="leftButtonLabel"
                    :name="name"
                    :disabled="disabled"
                    classes="peer-checked:bg-indigo-500 peer-checked:text-white peer-checked:border-indigo-200
                         border border-r-0 rounded-l-md bg-white dark:bg-gray-900
                         dark:border-gray-700 dark:peer-checked:bg-indigo-600
                         dark:peer-checked:border-indigo-600"
                    :checked="model == true"
                    @click="handleModelChange(true)"
                />
            </div>

            <div>
                <RadioButton
                    :id="`no_` + name"
                    :label="rightButtonLabel"
                    :name="name"
                    :disabled="disabled"
                    classes="peer-checked:bg-indigo-500 peer-checked:text-white peer-checked:border-indigo-200
                         border border-l-0 rounded-r-md bg-white dark:bg-gray-900
                         dark:border-gray-700 dark:peer-checked:bg-indigo-600
                         dark:peer-checked:border-indigo-600"
                    :checked="model == false"
                    @click="handleModelChange(false)"
                />
            </div>
        </div>
    </div>
</template>
