<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserForm from "./UserForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    user: Object,
    roles: Array,
});
</script>

<template>
    <Head title="Edit User" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-8">Edit User</h1>

        <UserForm
            :user="user"
            :roles="roles"
            submit-route="admin.users.update"
            submit-method="put"
        />
    </AdminLayout>
</template>
