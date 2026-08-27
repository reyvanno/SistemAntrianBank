<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AdminLayout>
        <div class="mx-auto max-w-7xl space-y-6">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div
                class="rounded-2xl border border-red-100 bg-white p-6 shadow-sm"
            >
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AdminLayout>
</template>