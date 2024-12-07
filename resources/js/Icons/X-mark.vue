<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
        stroke?: string;
    }>(),
    {
        classes: "w-6 h-6 flex-shrink-0",
        stroke: "1.6",
    }
);
</script>

<template>
    <svg
        fill="none"
        viewBox="0 0 24 24"
        :stroke-width="stroke"
        stroke="currentColor"
        :class="classes"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M6 18L18 6M6 6l12 12"
        />
    </svg>
</template>
