<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { confirmDelete } from "@/lib/swal";
import SearchBox from "@/Components/Shared/SearchBox.vue";
import DataTable from "@/Components/Shared/DataTable.vue";
import Pagination from "@/Components/Shared/Pagination.vue";
import DangerButton from "@/Components/DangerButton.vue";
import { can } from "@/lib/can";

const props = defineProps({
    users: Object,
    filters: Object,
});

const page = usePage();

const roleLabel = (roles) => {
    if (!roles.length) return "-";

    switch (roles[0].name) {
        case "admin":
            return "Administrator";

        case "teller":
            return "Teller";

        case "customer_service":
            return "Customer Service";

        default:
            return roles[0].name;
    }
};

const search = ref(props.filters.search ?? "");

watch(search, (value) => {
    router.get(
        route("admin.users.index"),
        {
            search: value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const destroy = async (id) => {

    const result = await confirmDelete();

    if (!result.isConfirmed) return;

    router.delete(
        route("admin.users.destroy", id)
    );

};
</script>

<template>
    <Head title="Master User" />

    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">
                Kelola User
            </h1>

            <Link
                v-if="can('user.create')"
                :href="route('admin.users.create')"
                class="bg-indigo-600 text-white px-5 py-3 rounded-lg"
            >
                Tambah
            </Link>
        </div>

        <SearchBox
            v-model="search"
            placeholder="Cari user..."
        />
        
        <DataTable :headers="['Nama', 'Username', 'Email', 'Role', 'Status', 'Aksi']">
                <tr
                    v-for="user in users.data"
                    :key="user.id"
                    class="border-b text-center"
                >
                    <td class="p-4">
                        {{ user.name }}
                    </td>

                    <td>
                        {{ user.username }}
                    </td>

                    <td>
                        {{ user.email }}
                    </td>

                    <td>
                        {{ roleLabel(user.roles) }}
                    </td>

                    <td>
                        <span
                            v-if="user.is_active"
                            class="text-green-600 font-semibold"
                        >
                            Aktif
                        </span>

                        <span
                            v-else
                            class="text-red-600 font-semibold"
                        >
                            Tidak Aktif
                        </span>
                    </td>

                    <td>
                        <div class="flex justify-center gap-2">
                            <Link
                                v-if="can('user.update')"
                                :href="route('admin.users.edit', user.id)"
                                class="bg-yellow-500 text-white px-4 py-2 rounded"
                            >
                                Edit
                            </Link>

                            <DangerButton
                                v-if="can('user.delete')"
                                @click="destroy(user.id)"
                            >
                                Hapus
                            </DangerButton>
                        </div>
                    </td>
                </tr>

                <tr v-if="users.data.length === 0">
                    <td colspan="5" class="py-8 text-gray-500">
                        Tidak ada data user.
                    </td>
                </tr>
        </DataTable>

        <Pagination :links="users.links" />
    </AdminLayout>
</template>