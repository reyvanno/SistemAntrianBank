<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { can } from "@/lib/can";
import { confirmDelete } from "@/lib/swal";

defineProps({
    roles: Object,
});

const destroy = async (id) => {
    const result = await confirmDelete();

    if (!result.isConfirmed) return;

    router.delete(route("admin.roles.destroy", id));
};
</script>

<template>
    <Head title="Role Management" />

    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Role Management
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola role dan permission pengguna.
                </p>
            </div>

            <Link
                v-if="can('role.create')"
                :href="route('admin.roles.create')"
                class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white transition hover:bg-indigo-700"
            >
                + Tambah Role
            </Link>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <table class="w-full">
                <thead>
                    <tr
                        class="border-b border-slate-200 bg-slate-50 text-left text-sm font-semibold text-slate-600"
                    >
                        <th class="px-6 py-4">
                            Role
                        </th>

                        <th class="px-6 py-4">
                            Deskripsi
                        </th>

                        <th class="px-6 py-4 text-center">
                            User
                        </th>

                        <th class="px-6 py-4 text-center">
                            Permission
                        </th>

                        <th class="px-6 py-4 text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="role in roles.data"
                        :key="role.id"
                        class="border-b border-slate-100 last:border-0 hover:bg-slate-50"
                    >
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">
                                {{ role.name }}
                            </div>
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ role.description || "-" }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span
                                class="rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700"
                            >
                                {{ role.users_count }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span
                                class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700"
                            >
                                {{ role.permissions_count }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                <Link
                                    v-if="can('role.update')"
                                    :href="
                                        route(
                                            'admin.roles.edit',
                                            role.id
                                        )
                                    "
                                    class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-yellow-600"
                                >
                                    Edit
                                </Link>

                                <button
                                    v-if="can('role.delete')"
                                    @click="destroy(role.id)"
                                    class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="roles.data.length === 0">
                        <td
                            colspan="5"
                            class="px-6 py-10 text-center text-slate-500"
                        >
                            Belum ada role.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center gap-2">
            <template
                v-for="link in roles.links"
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