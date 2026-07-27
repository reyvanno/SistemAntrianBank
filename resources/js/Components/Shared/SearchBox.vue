<script setup>
const model = defineModel({
    type: String,
    default: "",
});

defineProps({
    placeholder: {
        type: String,
        default: "Cari...",
    },
});
</script>

<template>
    <input
        v-model="model"
        :placeholder="placeholder"
        class="border rounded-lg px-4 py-3 w-full md:w-80"
    />
</template>