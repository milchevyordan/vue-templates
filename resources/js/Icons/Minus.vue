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
        fill="currentColor"
        viewBox="0 -960 960 960"
        stroke-width="1.8"
        stroke="currentColor"
        :class="classes"
    >
        <path d="M200-450v-60h560v60H200Z" />
    </svg>
</template>
