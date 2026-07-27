<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

const user = computed(() => page.props.auth.user);

const roleTitle = computed(() => {
    switch (user.value.role.name) {
        case "admin":
            return "Administrator";

        case "teller":
            return "Teller";

        case "customer_service":
            return "Customer Service";

        default:
            return "";
    }
});

const date = new Date().toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
});
</script>

<template>
    <header
        class="bg-white border-b h-20 px-8 flex justify-between items-center"
    >
        <h1 class="text-3xl font-bold">Dashboard {{ roleTitle }}</h1>

        <div class="flex items-center gap-4">
            <div class="border rounded-xl px-5 py-2">
                {{ date }}
            </div>

            <div class="border rounded-lg px-5 py-2 bg-green-500 text-white">
                ● Realtime
            </div>
        </div>
    </header>
</template>
