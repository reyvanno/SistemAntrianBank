<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PermissionForm from "./PermissionForm.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    permission: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Edit Permission - ${permission.name}`" />

    <AdminLayout>
        <!-- HEADER -->
        <div class="mb-8">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.permissions.index')"
                    class="text-sm font-medium text-slate-500 transition hover:text-indigo-600"
                >
                    Permission Management
                </Link>

                <span class="text-slate-400">/</span>

                <span class="text-sm font-medium text-slate-800">
                    Edit Permission
                </span>
            </div>

            <div class="mt-4">
                <h1 class="text-3xl font-bold text-slate-800">
                    Edit Permission
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Ubah nama permission yang tersedia dalam sistem.
                </p>
            </div>
        </div>

        <div class="max-w-2xl">
            <PermissionForm
                :permission="permission"
                submit-route="admin.permissions.update"
                submit-method="put"
            />
        </div>
    </AdminLayout>
</template>