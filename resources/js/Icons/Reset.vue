<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
        solid?: string;
    }>(),
    {
        classes: "w-6 h-6 flex-shrink-0",
    }
);
</script>

<template>
    <svg
        viewBox="0 0 21 21"
        fill="currentColor"
        stroke="currentColor"
        :class="classes"
        stroke-width="1.8"
    >
        <g stroke-width="0" />
        <g
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <g>
            <g
                fill="none"
                fill-rule="evenodd"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                transform="matrix(0 1 1 0 2.5 2.5)"
            >
                <path d="m3.98652376 1.07807068c-2.38377179 1.38514556-3.98652376 3.96636605-3.98652376 6.92192932 0 4.418278 3.581722 8 8 8s8-3.581722 8-8-3.581722-8-8-8" />
                <path
                    d="m4 1v4h-4"
                    transform="matrix(1 0 0 -1 0 6)"
                />
            </g>
        </g>
    </svg>
</template>
