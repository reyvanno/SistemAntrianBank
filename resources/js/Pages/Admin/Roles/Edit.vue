<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import RoleForm from "./RoleForm.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    role: {
        type: Object,
        required: true,
    },

    permissions: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="`Edit Role - ${role.name}`" />

    <AdminLayout>
        <!-- HEADER -->
        <div class="mb-8">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.roles.index')"
                    class="text-sm font-medium text-slate-500 transition hover:text-indigo-600"
                >
                    Role Management
                </Link>

                <span class="text-slate-400">/</span>

                <span class="text-sm font-medium text-slate-800">
                    Edit Role
                </span>
            </div>

            <div class="mt-4">
                <h1 class="text-3xl font-bold text-slate-800">
                    Edit Role
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Ubah informasi dan permission role
                    <span class="font-semibold text-slate-700">
                        {{ role.name }}
                    </span>.
                </p>
            </div>
        </div>

        <RoleForm
            :role="role"
            :permissions="permissions"
            submit-route="admin.roles.update"
            submit-method="put"
        />
    </AdminLayout>
</template>