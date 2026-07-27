<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head } from "@inertiajs/vue3";

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
        <div class="grid grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-3xl shadow p-6">
                <p class="text-gray-500">Total Antrian Hari Ini</p>

                <h1 class="text-5xl font-bold mt-3">
                    {{ statistics.total }}
                </h1>
            </div>

            <div class="bg-white rounded-3xl shadow p-6">
                <p class="text-gray-500">Antrian Aktif</p>

                <h1 class="text-5xl font-bold mt-3">
                    {{ statistics.active }}
                </h1>
            </div>

            <div class="bg-white rounded-3xl shadow p-6">
                <p class="text-gray-500">Antrian Selesai</p>

                <h1 class="text-5xl font-bold mt-3">
                    {{ statistics.finished }}
                </h1>
            </div>

            <div class="bg-white rounded-3xl shadow p-6">
                <p class="text-gray-500">Rata-rata Waktu</p>

                <h1 class="text-5xl font-bold mt-3">
                    {{ statistics.average }}m
                </h1>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-8">
            <div class="col-span-2">
                <div class="bg-white rounded-3xl shadow p-6">
                    <h2 class="text-2xl font-bold mb-5">Antrian Aktif</h2>

                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="py-3">Nomor</th>

                                <th>Layanan</th>

                                <th>Loket</th>

                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="queue in activeQueues"
                                :key="queue.id"
                                class="border-b text-center"
                            >
                                <td class="py-4 font-bold">
                                    {{ queue.queue_number }}
                                </td>

                                <td>
                                    {{ queue.service.name }}
                                </td>

                                <td>
                                    {{ queue.counter?.code ?? "-" }}
                                </td>

                                <td>
                                    <span
                                        :class="badgeClass(queue.status)"
                                        class="px-3 py-1 rounded-full text-sm"
                                    >
                                        {{ queue.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-3xl shadow p-6">
                    <h2 class="text-2xl font-bold mb-5">Status Loket</h2>

                    <div
                        v-for="counter in counterStatus"
                        :key="counter.id"
                        class="border rounded-xl p-4 mb-3"
                    >
                        <div class="flex justify-between">
                            <div>
                                <p class="font-bold">
                                    {{ counter.code }}
                                </p>

                                <p class="text-gray-500">
                                    {{ counter.service.name }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p
                                    v-if="counter.queues.length"
                                    class="font-bold"
                                >
                                    {{ counter.queues[0].queue_number }}
                                </p>

                                <p v-else class="text-gray-500">Kosong</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
