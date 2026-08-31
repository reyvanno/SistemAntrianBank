<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserForm from "./UserForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    user: {
        type: Object,
        required: true,
    },

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
    <Head title="Edit User" />

    <AdminLayout>
        <h1 class="mb-8 text-2xl font-bold">
            Edit User
        </h1>

        <UserForm
            :user="user"
            :roles="roles"
            :counters="counters"
            submit-route="admin.users.update"
            submit-method="put"
        />
    </AdminLayout>
</template>