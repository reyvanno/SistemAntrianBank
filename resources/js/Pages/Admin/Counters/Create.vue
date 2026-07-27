<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import CounterForm from "./CounterForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    services: Array,
});
</script>

<template>
    <Head title="Tambah Loket" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-8">
            Tambah Loket
        </h1>

        <CounterForm
            :services="services"
            submit-route="admin.counters.store"
            submit-method="post"
        />
    </AdminLayout>
</template>