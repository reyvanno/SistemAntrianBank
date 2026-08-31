<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import SearchBox from "@/Components/Shared/SearchBox.vue";
import DataTable from "@/Components/Shared/DataTable.vue";
import Pagination from "@/Components/Shared/Pagination.vue";

import {
    TicketIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

import { can } from "@/lib/can";

const props = defineProps({
    queues: {
        type: Object,
        required: true,
    },

    services: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(
    props.filters.search ?? ""
);

watch(
    search,
    (value) => {
        router.get(
            route("admin.queues.index"),
            {
                search: value,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }
);

const form = useForm({
    service_id: "",
});

const submit = () => {
    if (!form.service_id || form.processing) {
        return;
    }

    form.post(
        route("admin.queues.store"),
        {
            preserveScroll: true,

            onSuccess: () => {
                form.reset();
            },
        }
    );
};

const cancelQueue = (queue) => {
    if (queue.status !== "WAITING") {
        return;
    }

    if (
        !window.confirm(
            `Yakin ingin membatalkan nomor ${queue.queue_number}?`
        )
    ) {
        return;
    }

    router.post(
        route(
            "admin.queues.cancel",
            queue.id
        ),
        {},
        {
            preserveScroll: true,
        }
    );
};

const badge = (status) => {
    switch (status) {
        case "WAITING":
            return "bg-yellow-100 text-yellow-700";

        case "CALLED":
            return "bg-blue-100 text-blue-700";

        case "SERVING":
            return "bg-emerald-100 text-emerald-700";

        case "FINISHED":
            return "bg-slate-200 text-slate-700";

        case "SKIPPED":
            return "bg-red-100 text-red-700";

        case "CANCELLED":
            return "bg-gray-200 text-gray-600";

        default:
            return "bg-slate-100 text-slate-700";
    }
};

const statusLabel = (status) => {
    switch (status) {
        case "WAITING":
            return "Menunggu";

        case "CALLED":
            return "Dipanggil";

        case "SERVING":
            return "Melayani";

        case "FINISHED":
            return "Selesai";

        case "SKIPPED":
            return "Dilewati";

        case "CANCELLED":
            return "Dibatalkan";

        default:
            return status;
    }
};
</script>

<template>
    <Head title="Antrian" />

    <AdminLayout>

        <!-- HEADER -->
        <div class="mb-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Antrian
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Kelola dan pantau seluruh nomor antrian.
                    </p>
                </div>

                <div
                    class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600"
                >
                    {{ queues.total }} Antrian
                </div>

            </div>
        </div>

        <!-- CREATE QUEUE -->
        <div
            v-if="can('queue.create')"
            class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div class="mb-5 flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50"
                >
                    <TicketIcon
                        class="h-6 w-6 text-indigo-600"
                    />
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Ambil Nomor Antrian
                    </h2>

                    <p class="text-sm text-slate-500">
                        Buat nomor antrian secara manual.
                    </p>
                </div>

            </div>

            <form
                class="flex flex-col gap-3 sm:flex-row"
                @submit.prevent="submit"
            >
                <select
                    v-model="form.service_id"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 sm:w-80"
                >
                    <option value="">
                        Pilih Layanan
                    </option>

                    <option
                        v-for="service in services"
                        :key="service.id"
                        :value="service.id"
                    >
                        {{ service.code }} -
                        {{ service.name }}
                    </option>
                </select>

                <button
                    type="submit"
                    :disabled="
                        !form.service_id ||
                        form.processing
                    "
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? "Membuat..."
                            : "Ambil Nomor"
                    }}
                </button>
            </form>

            <p
                v-if="form.errors.service_id"
                class="mt-2 text-sm font-medium text-red-600"
            >
                {{ form.errors.service_id }}
            </p>
        </div>

        <!-- SEARCH -->
        <div class="mb-5">
            <SearchBox
                v-model="search"
                placeholder="Cari nomor antrian..."
            />
        </div>

        <!-- TABLE -->
        <div
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div class="mb-5">
                <h2 class="text-lg font-semibold text-slate-800">
                    Daftar Antrian
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Riwayat dan status nomor antrian.
                </p>
            </div>

            <DataTable
                :headers="[
                    'Nomor',
                    'Layanan',
                    'Loket',
                    'Status',
                    'Petugas',
                    'Aksi',
                ]"
            >
                <tr
                    v-for="queue in queues.data"
                    :key="queue.id"
                    class="border-b border-slate-100 last:border-0 hover:bg-slate-50"
                >
                    <!-- NUMBER -->
                    <td class="px-4 py-4 text-center">
                        <span class="font-bold text-slate-800">
                            {{ queue.queue_number }}
                        </span>
                    </td>

                    <!-- SERVICE -->
                    <td class="px-4 py-4 text-center">
                        <div class="font-medium text-slate-700">
                            {{ queue.service?.name ?? "-" }}
                        </div>

                        <div class="text-xs text-slate-400">
                            {{ queue.service?.code ?? "" }}
                        </div>
                    </td>

                    <!-- COUNTER -->
                    <td class="px-4 py-4 text-center text-slate-600">
                        {{ queue.counter?.code ?? "-" }}
                    </td>

                    <!-- STATUS -->
                    <td class="px-4 py-4 text-center">
                        <span
                            :class="badge(queue.status)"
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                        >
                            {{ statusLabel(queue.status) }}
                        </span>
                    </td>

                    <!-- USER -->
                    <td class="px-4 py-4 text-center text-slate-600">
                        {{ queue.handled_by?.name ?? "-" }}
                    </td>

                    <!-- ACTION -->
                    <td class="px-4 py-4 text-center">
                        <button
                            v-if="
                                can('queue.cancel') &&
                                queue.status === 'WAITING'
                            "
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                            @click="cancelQueue(queue)"
                        >
                            <XMarkIcon class="h-4 w-4" />

                            Batalkan
                        </button>

                        <span
                            v-else
                            class="text-xs text-slate-400"
                        >
                            -
                        </span>
                    </td>
                </tr>

                <!-- EMPTY -->
                <tr v-if="queues.data.length === 0">
                    <td
                        colspan="6"
                        class="px-4 py-12 text-center"
                    >
                        <p class="font-medium text-slate-600">
                            Tidak ada data antrian.
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            Belum ada antrian yang sesuai dengan pencarian.
                        </p>
                    </td>
                </tr>
            </DataTable>
        </div>

        <!-- PAGINATION -->
        <div class="mt-6">
            <Pagination
                :links="queues.links"
            />
        </div>

    </AdminLayout>
</template>