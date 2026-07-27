<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { confirmDelete } from "@/lib/swal";
import SearchBox from "@/Components/Shared/SearchBox.vue";

const props = defineProps({
    counters: Object,
    filters: Object,
});

const page = usePage();

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

        <table class="w-full bg-white rounded-xl overflow-hidden">
            <thead>
                <tr class="border-b">
                    <th class="p-4">Kode</th>

                    <th>Nama</th>

                    <th>Layanan</th>

                    <th>Status</th>

                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
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
                                :href="route('admin.counters.edit', counter.id)"
                                class="bg-yellow-500 text-white px-4 py-2 rounded"
                            >
                                Edit
                            </Link>

                            <button
                                @click="destroy(counter.id)"
                                class="bg-red-500 text-white px-4 py-2 rounded"
                            >
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="flex gap-2 mt-6">
            <Link
                v-for="link in counters.links"
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
