<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";
import { Head } from "@inertiajs/vue3";
import { can } from "@/lib/can.js";
import DataTable from "@/Components/Shared/DataTable.vue";

defineProps({
    statistics: Object,
    activeQueues: Array,
    counterStatus: Array,
    today: String,
});

const badgeClass = (status) => {
    switch (status) {
        case "WAITING":
            return "bg-yellow-100 text-yellow-700";

        case "CALLED":
            return "bg-blue-100 text-blue-700";

        case "SERVING":
            return "bg-green-100 text-green-700";

        case "FINISHED":
            return "bg-gray-200 text-gray-700";

        default:
            return "bg-red-100 text-red-700";
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <StatCard
                title="Total Antrian Hari Ini"
                :value="statistics.total"
                color="blue"
            />

            <StatCard
                title="Antrian Aktif"
                :value="statistics.active"
                color="amber"
            />

            <StatCard
                title="Antrian Selesai"
                :value="statistics.finished"
                color="emerald"
            />

            <StatCard
                title="Rata-rata Waktu"
                :value="`${statistics.average} Menit`"
                color="violet"
            />
        </div>

        <div class="col-span-2">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="mb-5 text-lg font-semibold text-slate-800">
                    Antrian Aktif
                </h2>

                <DataTable :headers="['Nomor', 'Layanan', 'Loket', 'Status']">
                    <tr
                        v-for="queue in activeQueues"
                        :key="queue.id"
                        class="border-b border-slate-100 last:border-0 hover:bg-slate-50"
                    >
                        <td
                            class="px-4 py-4 text-center font-bold text-slate-800"
                        >
                            {{ queue.queue_number }}
                        </td>

                        <td class="px-4 py-4 text-center text-slate-600">
                            {{ queue.service.name }}
                        </td>

                        <td class="px-4 py-4 text-center text-slate-600">
                            {{ queue.counter?.code ?? "-" }}
                        </td>

                        <td class="px-4 py-4 text-center">
                            <span
                                :class="badgeClass(queue.status)"
                                class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                            >
                                {{ queue.status }}
                            </span>
                        </td>
                    </tr>

                    <tr v-if="activeQueues.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-10 text-center text-sm text-slate-500"
                        >
                            Tidak ada antrian aktif.
                        </td>
                    </tr>
                </DataTable>
            </div>
        </div>
    </AdminLayout>
</template>
