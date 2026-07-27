<script setup>
defineProps({
    headers: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th
                        v-for="header in headers"
                        :key="header"
                        class="px-4 py-3 text-center font-semibold"
                    >
                        {{ header }}
                    </th>
                </tr>
            </thead>

            <tbody>
                <slot />
            </tbody>
        </table>
    </div>
</template>