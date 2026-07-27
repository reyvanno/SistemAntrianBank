<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ServiceForm from "./ServiceForm.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Tambah Layanan" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-8">Tambah Layanan</h1>

        <ServiceForm submit-route="admin.services.store" submit-method="post" />
    </AdminLayout>
</template>
