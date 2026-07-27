<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="flex flex-wrap gap-2 mt-6">
        <Link
            v-for="link in links"
            :key="link.label"
            :href="link.url ?? ''"
            v-html="link.label"
            class="border px-4 py-2 rounded-lg transition"
            :class="{
                'bg-indigo-600 text-white border-indigo-600': link.active,
                'pointer-events-none opacity-50': !link.url,
            }"
        />
    </div>
</template>