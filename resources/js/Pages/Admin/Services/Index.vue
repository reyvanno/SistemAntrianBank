<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { confirmDelete } from "@/lib/swal";
import SearchBox from "@/Components/Shared/SearchBox.vue";
import DataTable from "@/Components/Shared/DataTable.vue";

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
                            :href="route('admin.services.edit', service.id)"
                            class="bg-yellow-500 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <button
                            @click="destroy(service.id)"
                            class="bg-red-500 text-white px-4 py-2 rounded"
                        >
                            Hapus
                        </button>
                    </div>
                </td>
            </tr>
        </DataTable>
        
        <div class="flex gap-2 mt-6">
            <Link
                v-for="link in services.links"
                :key="link.label"
                :href="link.url ?? ''"
                v-html="link.label"
                class="border px-4 py-2 rounded"
                :class="{
                    'bg-indigo-600 text-white': link.active,

                    'pointer-events-none opacity-50': !link.url,
                }"
            >
            </Link>
        </div>
    </AdminLayout>
</template>
