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
    permissions: {
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
        route("admin.permissions.index"),
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

        <!-- SEARCH -->
        <SearchBox
            v-model="search"
            placeholder="Cari permission..."
        />

        <!-- TABLE -->
        <DataTable
            :headers="[
                'Permission',
                'Guard',
                'Aksi',
            ]"
        >
            <tr
                v-for="permission in permissions.data"
                :key="permission.id"
                class="border-b text-center"
            >
                <!-- NAME -->
                <td class="p-4">
                    <span
                        class="font-semibold text-slate-800"
                    >
                        {{ permission.name }}
                    </span>
                </td>

                <!-- GUARD -->
                <td class="px-4 py-4">
                    <span
                        class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600"
                    >
                        {{ permission.guard_name }}
                    </span>
                </td>

                <!-- ACTION -->
                <td class="px-4 py-4">
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

                        <DangerButton
                            v-if="can('permission.delete')"
                            @click="destroy(permission.id)"
                        >
                            Hapus
                        </DangerButton>
                    </div>
                </td>
            </tr>

            <!-- EMPTY -->
            <tr
                v-if="
                    permissions.data.length === 0
                "
            >
                <td
                    colspan="3"
                    class="px-4 py-10 text-center text-slate-500"
                >
                    <template v-if="search">
                        Permission dengan kata
                        <span
                            class="font-semibold text-slate-700"
                        >
                            "{{ search }}"
                        </span>
                        tidak ditemukan.
                    </template>

                    <template v-else>
                        Belum ada permission.
                    </template>
                </td>
            </tr>
        </DataTable>

        <!-- PAGINATION -->
        <Pagination
            :links="permissions.links"
        />
    </AdminLayout>
</template>