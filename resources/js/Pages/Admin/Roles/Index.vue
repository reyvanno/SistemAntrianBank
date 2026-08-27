<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import { can } from "@/lib/can";
import { confirmDelete } from "@/lib/confirm";

import SearchBox from "@/Components/Shared/SearchBox.vue";
import DataTable from "@/Components/Shared/DataTable.vue";
import Pagination from "@/Components/Shared/Pagination.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    roles: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: "",
        }),
    },
});

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

const search = ref(
    props.filters.search ?? ""
);

watch(search, (value) => {
    router.get(
        route("admin.roles.index"),
        {
            search: value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const destroy = async (id) => {
    const result = await confirmDelete();

    if (!result.isConfirmed) return;

    router.delete(
        route("admin.roles.destroy", id)
    );
};
</script>

<template>
    <Head title="Role Management" />

    <AdminLayout>
        <!-- HEADER -->
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

        <!-- SEARCH -->
        <SearchBox
            v-model="search"
            placeholder="Cari role..."
        />

        <!-- TABLE -->
        <DataTable
            :headers="[
                'Role',
                'Deskripsi',
                'User',
                'Permission',
                'Aksi',
            ]"
        >
            <tr
                v-for="role in roles.data"
                :key="role.id"
                class="border-b text-center"
            >
                <!-- ROLE -->
                <td class="p-4">
                    <div class="font-semibold text-slate-800">
                        {{ role.name }}
                    </div>
                </td>

                <!-- DESCRIPTION -->
                <td class="px-4 py-4 text-sm text-slate-500">
                    {{ role.description || "-" }}
                </td>

                <!-- USERS -->
                <td class="px-4 py-4">
                    <span
                        class="rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700"
                    >
                        {{ role.users_count }}
                    </span>
                </td>

                <!-- PERMISSIONS -->
                <td class="px-4 py-4">
                    <span
                        class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700"
                    >
                        {{ role.permissions_count }}
                    </span>
                </td>

                <!-- ACTION -->
                <td class="px-4 py-4">
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

                        <DangerButton
                            v-if="can('role.delete')"
                            @click="destroy(role.id)"
                        >
                            Hapus
                        </DangerButton>
                    </div>
                </td>
            </tr>

            <!-- EMPTY -->
            <tr v-if="roles.data.length === 0">
                <td
                    colspan="5"
                    class="px-4 py-10 text-center text-slate-500"
                >
                    <template v-if="search">
                        Role dengan kata
                        <span class="font-semibold text-slate-700">
                            "{{ search }}"
                        </span>
                        tidak ditemukan.
                    </template>

                    <template v-else>
                        Belum ada role.
                    </template>
                </td>
            </tr>
        </DataTable>

        <!-- PAGINATION -->
        <Pagination :links="roles.links" />
    </AdminLayout>
</template>