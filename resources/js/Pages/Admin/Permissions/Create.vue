<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PermissionForm from "./PermissionForm.vue";
import { Head, Link } from "@inertiajs/vue3";

</script>

<template>
    <Head title="Tambah Permission" />

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
                    Tambah Permission
                </span>
            </div>

            <div class="mt-4">
                <h1 class="text-3xl font-bold text-slate-800">
                    Tambah Permission
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Tambahkan jenis akses baru yang dapat diberikan kepada role.
                </p>
            </div>
        </div>

        <div class="max-w-2xl">
            <PermissionForm
                submit-route="admin.permissions.store"
                submit-method="post"
            />
        </div>
    </AdminLayout>
</template>