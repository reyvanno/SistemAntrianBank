<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { can } from "@/lib/can";
import { confirmDelete } from "@/lib/swal";

defineProps({
    permissions: {
        type: Object,
        required: true,
    },
});

const destroy = async (id) => {
    const result = await confirmDelete();

    if (!result.isConfirmed) return;

    router.delete(
        route("admin.permissions.destroy", id)
    );
};
</script>

<template>
    <Head title="Permission Management" />

    <AdminLayout>
        <!-- HEADER -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Permission Management
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola permission yang tersedia dalam sistem.
                </p>
            </div>

            <Link
                v-if="can('permission.create')"
                :href="route('admin.permissions.create')"
                class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white transition hover:bg-indigo-700"
            >
                + Tambah Permission
            </Link>
        </div>

        <!-- INFO -->
        <div
            class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 px-5 py-4"
        >
            <p class="text-sm text-indigo-800">
                <span class="font-semibold">
                    Permission
                </span>
                menentukan jenis akses yang tersedia dalam sistem.
                Pengaturan permission yang dimiliki setiap role dilakukan
                melalui halaman Role Management.
            </p>
        </div>

        <!-- TABLE -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <table class="w-full">
                <thead>
                    <tr
                        class="border-b border-slate-200 bg-slate-50 text-left text-sm font-semibold text-slate-600"
                    >
                        <th class="px-6 py-4">
                            Permission
                        </th>

                        <th class="px-6 py-4">
                            Modul
                        </th>

                        <th class="px-6 py-4 text-center">
                            Digunakan Role
                        </th>

                        <th class="px-6 py-4 text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="permission in permissions.data"
                        :key="permission.id"
                        class="border-b border-slate-100 last:border-0 hover:bg-slate-50"
                    >
                        <!-- NAME -->
                        <td class="px-6 py-4">
                            <div
                                class="font-semibold text-slate-800"
                            >
                                {{ permission.name }}
                            </div>

                            <div class="mt-1 text-xs text-slate-400">
                                ID: {{ permission.id }}
                            </div>
                        </td>

                        <!-- MODULE -->
                        <td class="px-6 py-4">
                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700"
                            >
                                {{ permission.name.split(".")[0] }}
                            </span>
                        </td>

                        <!-- ROLE COUNT -->
                        <td class="px-6 py-4 text-center">
                            <span
                                class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700"
                            >
                                {{ permission.roles_count }}
                            </span>
                        </td>

                        <!-- ACTION -->
                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                <Link
                                    v-if="can('permission.update')"
                                    :href="
                                        route(
                                            'admin.permissions.edit',
                                            permission.id
                                        )
                                    "
                                    class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-yellow-600"
                                >
                                    Edit
                                </Link>

                                <button
                                    v-if="can('permission.delete')"
                                    @click="destroy(permission.id)"
                                    class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- EMPTY -->
                    <tr
                        v-if="permissions.data.length === 0"
                    >
                        <td
                            colspan="4"
                            class="px-6 py-10 text-center text-slate-500"
                        >
                            Belum ada permission.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div
            v-if="permissions.links.length > 3"
            class="mt-6 flex justify-center gap-2"
        >
            <template
                v-for="link in permissions.links"
                :key="link.label"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm transition hover:bg-slate-50"
                    :class="{
                        'bg-indigo-600 text-white':
                            link.active,
                    }"
                />

                <span
                    v-else
                    v-html="link.label"
                    class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-400"
                />
            </template>
        </div>
    </AdminLayout>
</template>