<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { confirmDelete } from "@/lib/confirm";
import SearchBox from "@/Components/Shared/SearchBox.vue";
import DataTable from "@/Components/Shared/DataTable.vue";
import Pagination from "@/Components/Shared/Pagination.vue";
import DangerButton from "@/Components/DangerButton.vue";
import { can } from "@/lib/can";

const props = defineProps({
    services: Object,
    filters: Object,
});

const destroy = async (id) => {
    const result = await confirmDelete();

    if (!result.isConfirmed) return;

    router.delete(route("admin.services.destroy", id));
};

const search = ref(props.filters.search ?? "");

watch(search, (value) => {
    router.get(
        route("admin.services.index"),

        {
            search: value,
        },

        {
            preserveState: true,

            replace: true,
        },
    );
});

const page = usePage();
</script>

<template>
    <Head title="Master Service" />

    <AdminLayout>
        <div class="flex justify-between mb-6">
            <h1 class="text-3xl font-bold">Layanan</h1>

            <Link
                v-if="can('service.create')"
                :href="route('admin.services.create')"
                class="bg-indigo-600 text-white px-5 py-3 rounded-lg"
            >
                Tambah
            </Link>
        </div>

        <SearchBox v-model="search" placeholder="Cari layanan..." />

        <DataTable :headers="['Kode', 'Nama', 'Aksi']">
            <tr
                v-for="service in services.data"
                :key="service.id"
                class="border-b text-center"
            >
                <td class="p-4">
                    {{ service.code }}
                </td>

                <td>
                    {{ service.name }}
                </td>

                <td>
                    <div class="flex justify-center gap-2">
                        <Link
                            v-if="can('service.update')"
                            :href="route('admin.services.edit', service.id)"
                            class="bg-yellow-500 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <DangerButton  
                            v-if="can('service.delete')"
                            @click="destroy(service.id)"
                        >
                            Hapus
                        </DangerButton>
                    </div>
                </td>
            </tr>
        </DataTable>
        
        <Pagination :links="services.links" />
    </AdminLayout>
</template>
