<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserForm from "./UserForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    roles: {
        type: Array,
        default: () => [],
    },

    counters: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Tambah User" />

    <AdminLayout>
        <h1 class="mb-8 text-2xl font-bold">
            Tambah User
        </h1>

        <UserForm
            :roles="roles"
            :counters="counters"
            submit-route="admin.users.store"
            submit-method="post"
        />
    </AdminLayout>
</template>