<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
    }>(),
    {
        classes: "w-6 h-6 flex-shrink-0",
    }
);
</script>

<template>
    <svg
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.8"
        stroke="currentColor"
        :class="classes"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 6v12m6-6H6"
        />
    </svg>
</template>
