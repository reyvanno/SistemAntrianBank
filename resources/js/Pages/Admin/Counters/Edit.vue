<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import CounterForm from "./CounterForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    counter: Object,
    services: Array,
});
</script>

<template>
    <Head title="Edit Loket" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-8">
            Edit Loket
        </h1>

        <CounterForm
            :counter="counter"
            :services="services"
            submit-route="admin.counters.update"
            submit-method="put"
        />
    </AdminLayout>
</template>