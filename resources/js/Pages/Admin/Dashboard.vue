<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";
import MyCounterCard from "@/Components/Dashboard/MyCounterCard.vue";
import CounterStatusCard from "@/Components/Dashboard/CounterStatusCard.vue";
import DataTable from "@/Components/Shared/DataTable.vue";

import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    statistics: {
        type: Object,
        required: true,
    },

    activeQueues: {
        type: Array,
        default: () => [],
    },

    counterStatus: {
        type: Array,
        default: () => [],
    },

    myCounter: {
        type: Object,
        default: null,
    },

    today: {
        type: String,
        default: "",
    },
});

const page = usePage();

const user = computed(() => page.props.auth.user);

const isAdmin = computed(() => {
    return user.value?.role === "admin";
});

const processing = ref(false);

/*
|--------------------------------------------------------------------------
| Queue Status
|--------------------------------------------------------------------------
*/

const queueStatusLabel = (status) => {
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

const queueStatusClass = (status) => {
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

/*
|--------------------------------------------------------------------------
| Queue Actions
|--------------------------------------------------------------------------
*/

const submitAction = (action) => {
    if (processing.value) {
        return;
    }

    processing.value = true;

    router.post(
        route(`admin.queues.${action}`),
        {},
        {
            preserveScroll: true,

            onFinish: () => {
                processing.value = false;
            },
        },
    );
};

const callNext = () => {
    submitAction("call");
};

const recall = () => {
    submitAction("recall");
};

const start = () => {
    submitAction("start");
};

const finish = () => {
    submitAction("finish");
};

const skip = () => {
    if (
        !window.confirm(
            "Nomor ini sudah dipanggil 3 kali. Yakin ingin melewati antrian?",
        )
    ) {
        return;
    }

    processing.value = true;

    router.post(
        route("admin.queues.skip"),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <!-- =========================================================
             STATISTICS
        ========================================================== -->
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
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

        <!-- =========================================================
             MY COUNTER
        ========================================================== -->
        <div v-if="!isAdmin && myCounter" class="mb-8">
            <MyCounterCard
                :counter="myCounter"
                :processing="processing"
                @call="callNext"
                @recall="recall"
                @start="start"
                @finish="finish"
                @skip="skip"
            />
        </div>

        <!-- =========================================================
             COUNTER STATUS
        ========================================================== -->
        <section
            class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">
                        Status Loket
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Kondisi dan aktivitas setiap loket.
                    </p>
                </div>

                <span
                    class="w-fit rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600"
                >
                    {{ counterStatus.length }} Loket
                </span>
            </div>

            <div
                v-if="counterStatus.length"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >
                <CounterStatusCard
                    v-for="counter in counterStatus"
                    :key="counter.id"
                    :counter="counter"
                />
            </div>

            <div
                v-else
                class="rounded-xl border border-dashed border-slate-300 px-6 py-12 text-center"
            >
                <p class="text-sm text-slate-500">
                    Belum ada loket yang tersedia.
                </p>
            </div>
        </section>

        <!-- =========================================================
             ACTIVE QUEUES
        ========================================================== -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">
                        Antrian Aktif
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Daftar antrian yang masih dalam proses hari ini.
                    </p>
                </div>

                <span
                    class="w-fit rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700"
                >
                    {{ activeQueues.length }} Antrian
                </span>
            </div>

            <div class="overflow-x-auto">
                <DataTable :headers="['Nomor', 'Layanan', 'Loket', 'Status']">
                    <tr
                        v-for="queue in activeQueues"
                        :key="queue.id"
                        class="border-b border-slate-100 last:border-0 transition hover:bg-slate-50"
                    >
                        <!-- NUMBER -->
                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            <span
                                class="text-lg font-black tracking-wide text-indigo-700"
                            >
                                {{ queue.queue_number }}
                            </span>
                        </td>

                        <!-- SERVICE -->
                        <td
                            class="whitespace-nowrap px-4 py-4 text-center text-sm font-medium text-slate-600"
                        >
                            {{ queue.service?.name ?? "-" }}
                        </td>

                        <!-- COUNTER -->
                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            <span
                                v-if="queue.counter"
                                class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600"
                            >
                                {{ queue.counter.code }}
                            </span>

                            <span v-else class="text-sm text-slate-400">
                                Menunggu
                            </span>
                        </td>

                        <!-- STATUS -->
                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            <span
                                :class="queueStatusClass(queue.status)"
                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                            >
                                {{ queueStatusLabel(queue.status) }}
                            </span>
                        </td>
                    </tr>

                    <!-- EMPTY -->
                    <tr v-if="activeQueues.length === 0">
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100"
                            >
                                <span class="text-xl"> — </span>
                            </div>

                            <p class="mt-3 text-sm font-medium text-slate-600">
                                Tidak ada antrian aktif.
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Semua antrian saat ini sudah selesai diproses.
                            </p>
                        </td>
                    </tr>
                </DataTable>
            </div>
        </section>
    </AdminLayout>
</template>
