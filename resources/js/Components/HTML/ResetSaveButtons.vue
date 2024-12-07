<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

defineProps<{
    processing: boolean;
    recentlySuccessful: boolean;
    isDelete?: boolean;
}>();
</script>

<template>
    <div class="flex items-center gap-4">
        <SecondaryButton
            :disabled="processing"
            @click="$emit('reset')"
        >
            Reset
        </SecondaryButton>

        <PrimaryButton :disabled="processing">
            {{ isDelete ? 'Delete' : 'Save' }}
        </PrimaryButton>

        <Transition
            enter-active-class="transition ease-in-out"
            enter-from-class="opacity-0"
            leave-active-class="transition ease-in-out"
            leave-to-class="opacity-0"
        >
            <p
                v-if="recentlySuccessful"
                class="text-sm text-gray-600 dark:text-gray-400"
            >
                Saved.
            </p>
        </Transition>
    </div>
</template>
