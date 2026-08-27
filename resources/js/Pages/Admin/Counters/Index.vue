<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { confirmDelete } from "@/lib/confirm";
import SearchBox from "@/Components/Shared/SearchBox.vue";
import DataTable from "@/Components/Shared/DataTable.vue";
import Pagination from "@/Components/Shared/Pagination.vue";
import DangerButton from "@/Components/DangerButton.vue";
import { can } from "@/lib/can";

const props = defineProps({
    counters: Object,
    filters: Object,
});

const search = ref(props.filters.search ?? "");

watch(search, (value) => {
    router.get(
        route("admin.counters.index"),
        {
            search: value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const destroy = async (id) => {
    const result = await confirmDelete();

    if (!result.isConfirmed) return;

    router.delete(
        route("admin.counters.destroy", id)
    );
};
</script>

<template>
    <Head title="Master Counter" />

    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Loket</h1>

            <Link
                v-if="can('counter.create')"
                :href="route('admin.counters.create')"
                class="bg-indigo-600 text-white px-5 py-3 rounded-lg"
            >
                Tambah
            </Link>
        </div>

        <SearchBox
            v-model="search"
            placeholder="Cari Counter..."
        />

        <DataTable :headers="['Kode', 'Nama', 'Layanan', 'Status', 'Aksi']">
                <tr
                    v-for="counter in counters.data"
                    :key="counter.id"
                    class="border-b text-center"
                >
                    <td class="p-4">
                        {{ counter.code }}
                    </td>

                    <td>
                        {{ counter.name }}
                    </td>

                    <td>
                        {{ counter.service.name }}
                    </td>

                    <td>
                        <span
                            v-if="counter.is_active"
                            class="text-green-600 font-semibold"
                        >
                            Aktif
                        </span>

                        <span v-else class="text-red-600 font-semibold">
                            Nonaktif
                        </span>
                    </td>

                    <td>
                        <div class="flex justify-center gap-2">
                            <Link
                                v-if="can('counter.update')"
                                :href="route('admin.counters.edit', counter.id)"
                                class="bg-yellow-500 text-white px-4 py-2 rounded"
                            >
                                Edit
                            </Link>

                            <DangerButton
                                v-if="can('counter.delete')"
                                @click="destroy(counter.id)"
                            >
                                Hapus
                            </DangerButton>
                        </div>
                    </td>
                </tr>
        </DataTable>

        <Pagination :links="counters.links" />
    </AdminLayout>
</template>
