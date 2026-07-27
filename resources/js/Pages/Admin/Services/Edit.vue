<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ServiceForm from "./ServiceForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    service: Object,
});
</script>

<template>
    <Head title="Edit Layanan" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-8">Edit Layanan</h1>

        <ServiceForm
            :service="service"
            submit-route="admin.services.update"
            submit-method="put"
        />
    </AdminLayout>
</template>
