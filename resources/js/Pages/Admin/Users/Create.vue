<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserForm from "./UserForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    roles: Array,
});
</script>

<template>
    <Head title="Tambah User" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-8">Tambah User</h1>

        <UserForm
            :roles="roles"
            submit-route="admin.users.store"
            submit-method="post"
        />
    </AdminLayout>
</template>
