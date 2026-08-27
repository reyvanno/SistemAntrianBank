<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import RoleForm from "./RoleForm.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    permissions: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Tambah Role" />

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
                    Tambah Role
                </span>
            </div>

            <div class="mt-4">
                <h1 class="text-3xl font-bold text-slate-800">
                    Tambah Role
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Buat role baru dan tentukan permission yang dimilikinya.
                </p>
            </div>
        </div>

        <RoleForm
            :permissions="permissions"
            submit-route="admin.roles.store"
            submit-method="post"
        />
    </AdminLayout>
</template>