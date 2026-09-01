<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";
import MyCounterCard from "@/Components/Dashboard/MyCounterCard.vue";
import DataTable from "@/Components/Shared/DataTable.vue";

import {
    Head,
    router,
    usePage,
} from "@inertiajs/vue3";

import {
    computed,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
} from "vue";

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| REACTIVE DASHBOARD DATA
|--------------------------------------------------------------------------
|
| PENTING:
|
| Setelah ini template HARUS menggunakan:
|
| dashboardData.statistics
| dashboardData.activeQueues
| dashboardData.counterStatus
| dashboardData.myCounter
|
| Jangan lagi menggunakan props langsung.
|
|--------------------------------------------------------------------------
*/

const dashboardData = reactive({
    statistics: {
        ...props.statistics,
    },

    activeQueues: [
        ...props.activeQueues,
    ],

    counterStatus: [
        ...props.counterStatus,
    ],

    myCounter: props.myCounter
        ? {
            ...props.myCounter,
        }
        : null,

    today: props.today,
});

/*
|--------------------------------------------------------------------------
| AUTH USER
|--------------------------------------------------------------------------
*/

const page = usePage();

const user = computed(() => {
    return page.props.auth?.user;
});

const isAdmin = computed(() => {
    return user.value?.role === "admin";
});

/*
|--------------------------------------------------------------------------
| PROCESSING
|--------------------------------------------------------------------------
*/

const processing = ref(false);

/*
|--------------------------------------------------------------------------
| DASHBOARD POLLING
|--------------------------------------------------------------------------
*/

let dashboardTimer = null;

let dashboardPolling = false;

/*
 * Polling setiap 1.5 detik.
 *
 * Ini cukup cepat untuk dashboard terasa real-time,
 * tetapi tidak terlalu agresif ke server.
 */
const DASHBOARD_POLL_INTERVAL = 1500;

/**
 * Mengambil data dashboard terbaru.
 *
 * TIDAK melakukan reload halaman.
 *
 * Hanya mengganti data reactive.
 */
const refreshDashboard = async () => {
    /*
     * Jangan membuat request baru jika request
     * sebelumnya masih berjalan.
     */
    if (dashboardPolling) {
        return;
    }

    dashboardPolling = true;

    try {
        const response = await fetch(
            route("admin.dashboard.data"),
            {
                method: "GET",

                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },

                credentials: "same-origin",

                cache: "no-store",
            },
        );

        if (!response.ok) {
            throw new Error(
                `Dashboard polling gagal: HTTP ${response.status}`,
            );
        }

        const data = await response.json();

        /*
        |--------------------------------------------------------------------------
        | UPDATE STATISTICS
        |--------------------------------------------------------------------------
        */

        dashboardData.statistics =
            data.statistics ?? {
                total: 0,
                active: 0,
                finished: 0,
                average: 0,
            };

        /*
        |--------------------------------------------------------------------------
        | UPDATE ACTIVE QUEUES
        |--------------------------------------------------------------------------
        */

        dashboardData.activeQueues =
            data.activeQueues ?? [];

        /*
        |--------------------------------------------------------------------------
        | UPDATE COUNTER STATUS
        |--------------------------------------------------------------------------
        */

        dashboardData.counterStatus =
            data.counterStatus ?? [];

        /*
        |--------------------------------------------------------------------------
        | UPDATE MY COUNTER
        |--------------------------------------------------------------------------
        */

        dashboardData.myCounter =
            data.myCounter ?? null;

        /*
        |--------------------------------------------------------------------------
        | UPDATE TODAY
        |--------------------------------------------------------------------------
        */

        dashboardData.today =
            data.today ?? "";

    } catch (error) {
        console.error(
            "[Dashboard] Gagal mengambil data:",
            error,
        );
    } finally {
        dashboardPolling = false;
    }
};

/*
|--------------------------------------------------------------------------
| QUEUE STATUS
|--------------------------------------------------------------------------
*/

const queueStatusLabel = (status) => {
    switch (status) {
        case "WAITING":
            return "Menunggu";

        case "CALLED":
            return "Dipanggil";

        case "SERVING":
            return "Dilayani";

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
| COUNTER STATUS
|--------------------------------------------------------------------------
*/

const counterStatusLabel = (status) => {
    switch (status) {
        case "AVAILABLE":
            return "Tersedia";

        case "CALLED":
            return "Dipanggil";

        case "SERVING":
            return "Melayani";

        case "INACTIVE":
            return "Tidak Aktif";

        default:
            return status;
    }
};

const counterStatusClass = (status) => {
    switch (status) {
        case "AVAILABLE":
            return "bg-emerald-100 text-emerald-700";

        case "CALLED":
            return "bg-blue-100 text-blue-700";

        case "SERVING":
            return "bg-amber-100 text-amber-700";

        case "INACTIVE":
            return "bg-red-100 text-red-700";

        default:
            return "bg-slate-100 text-slate-700";
    }
};

/*
|--------------------------------------------------------------------------
| QUEUE ACTIONS
|--------------------------------------------------------------------------
*/

/**
 * Menjalankan action queue.
 *
 * Setelah action berhasil:
 *
 * call
 * recall
 * start
 * finish
 * skip
 *
 * dashboard langsung mengambil data terbaru.
 *
 * Jadi tidak perlu menunggu polling berikutnya.
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

            /*
             * Jangan biarkan Inertia melakukan
             * reload data dashboard secara otomatis.
             */
            preserveState: true,

            onSuccess: async () => {
                /*
                 * Setelah action berhasil,
                 * langsung ambil data terbaru.
                 */
                await refreshDashboard();
            },

            onError: (errors) => {
                console.error(
                    `[Dashboard] Action ${action} gagal:`,
                    errors,
                );
            },

            onFinish: () => {
                processing.value = false;
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| CALL
|--------------------------------------------------------------------------
*/

const callNext = () => {
    submitAction("call");
};

/*
|--------------------------------------------------------------------------
| RECALL
|--------------------------------------------------------------------------
*/

const recall = () => {
    submitAction("recall");
};

/*
|--------------------------------------------------------------------------
| START
|--------------------------------------------------------------------------
*/

const start = () => {
    submitAction("start");
};

/*
|--------------------------------------------------------------------------
| FINISH
|--------------------------------------------------------------------------
*/

const finish = () => {
    submitAction("finish");
};

/*
|--------------------------------------------------------------------------
| SKIP
|--------------------------------------------------------------------------
*/

const skip = () => {
    if (
        !window.confirm(
            "Nomor ini sudah dipanggil 3 kali. Yakin ingin melewati antrian?",
        )
    ) {
        return;
    }

    submitAction("skip");
};

/*
|--------------------------------------------------------------------------
| LIFECYCLE
|--------------------------------------------------------------------------
*/

onMounted(() => {
    /*
     * Ambil data terbaru saat dashboard
     * pertama kali dibuka.
     */
    refreshDashboard();

    /*
     * Polling real-time.
     */
    dashboardTimer = window.setInterval(
        refreshDashboard,
        DASHBOARD_POLL_INTERVAL,
    );
});

onBeforeUnmount(() => {
    if (dashboardTimer) {
        window.clearInterval(
            dashboardTimer,
        );

        dashboardTimer = null;
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>

        <!-- =========================================================
             STATISTICS
        ========================================================== -->

        <div
            class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4"
        >
            <StatCard
                title="Total Antrian Hari Ini"
                :value="dashboardData.statistics.total"
                color="blue"
            />

            <StatCard
                title="Antrian Aktif"
                :value="dashboardData.statistics.active"
                color="amber"
            />

            <StatCard
                title="Antrian Selesai"
                :value="dashboardData.statistics.finished"
                color="emerald"
            />

            <StatCard
                title="Rata-rata Waktu"
                :value="`${dashboardData.statistics.average} Menit`"
                color="violet"
            />
        </div>


        <!-- =========================================================
             MY COUNTER
        ========================================================== -->

        <div
            v-if="!isAdmin && dashboardData.myCounter"
            class="mb-6"
        >
            <MyCounterCard
                :counter="dashboardData.myCounter"
                :processing="processing"
                @call="callNext"
                @recall="recall"
                @start="start"
                @finish="finish"
                @skip="skip"
            />
        </div>


        <!-- =========================================================
             MAIN DASHBOARD AREA
        ========================================================== -->

        <div
            class="grid min-h-0 grid-cols-1 gap-6 lg:h-[560px] lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.85fr)]"
        >

            <!-- =====================================================
                 ACTIVE QUEUES
            ====================================================== -->

            <section
                class="flex min-h-[500px] min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:min-h-0"
            >

                <!-- HEADER -->

                <div
                    class="flex shrink-0 items-center justify-between px-6 py-5"
                >
                    <div class="min-w-0">
                        <h2
                            class="text-lg font-semibold text-slate-800"
                        >
                            Antrian Aktif
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Daftar antrian yang masih dalam proses hari ini.
                        </p>
                    </div>

                    <span
                        class="ml-4 shrink-0 rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700"
                    >
                        {{ dashboardData.activeQueues.length }}
                        Antrian
                    </span>
                </div>


                <!-- TABLE -->

                <div
                    class="min-h-0 flex-1 overflow-y-auto overflow-x-auto"
                >
                    <DataTable
                        :headers="[
                            'Nomor',
                            'Layanan',
                            'Loket',
                            'Status',
                        ]"
                    >

                        <tr
                            v-for="queue in dashboardData.activeQueues"
                            :key="queue.id"
                            class="transition hover:bg-slate-50"
                        >

                            <!-- NOMOR -->

                            <td
                                class="whitespace-nowrap px-4 py-4 text-center"
                            >
                                <span
                                    class="text-lg font-black tracking-wide text-indigo-700"
                                >
                                    {{ queue.queue_number }}
                                </span>
                            </td>


                            <!-- LAYANAN -->

                            <td
                                class="whitespace-nowrap px-4 py-4 text-center text-sm font-medium text-slate-600"
                            >
                                {{
                                    queue.service?.name ??
                                    "-"
                                }}
                            </td>


                            <!-- LOKET -->

                            <td
                                class="whitespace-nowrap px-4 py-4 text-center"
                            >
                                <span
                                    v-if="queue.counter"
                                    class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600"
                                >
                                    {{ queue.counter.code }}
                                </span>

                                <span
                                    v-else
                                    class="text-sm text-slate-400"
                                >
                                    Menunggu
                                </span>
                            </td>


                            <!-- STATUS -->

                            <td
                                class="whitespace-nowrap px-4 py-4 text-center"
                            >
                                <span
                                    :class="
                                        queueStatusClass(
                                            queue.status,
                                        )
                                    "
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                >
                                    {{
                                        queueStatusLabel(
                                            queue.status,
                                        )
                                    }}
                                </span>
                            </td>

                        </tr>


                        <!-- EMPTY -->

                        <tr
                            v-if="
                                dashboardData.activeQueues.length ===
                                0
                            "
                        >
                            <td
                                colspan="4"
                                class="h-[390px] px-6 text-center align-middle"
                            >
                                <div
                                    class="mx-auto flex max-w-md flex-col items-center justify-center"
                                >
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100"
                                    >
                                        <span
                                            class="text-xl font-medium text-slate-400"
                                        >
                                            —
                                        </span>
                                    </div>

                                    <p
                                        class="mt-3 text-sm font-medium text-slate-600"
                                    >
                                        Tidak ada antrian aktif.
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-400"
                                    >
                                        Semua antrian saat ini sudah selesai
                                        diproses.
                                    </p>
                                </div>
                            </td>
                        </tr>

                    </DataTable>
                </div>


                <!-- FOOTER -->

                <div
                    class="flex h-[48px] shrink-0 items-center justify-between border-t border-slate-100 bg-slate-50 px-6"
                >
                    <span
                        class="text-xs text-slate-500"
                    >
                        Menampilkan antrian aktif
                    </span>

                    <span
                        class="text-xs font-semibold text-slate-600"
                    >
                        {{ dashboardData.activeQueues.length }}
                        data
                    </span>
                </div>

            </section>


            <!-- =====================================================
                 COUNTER STATUS
            ====================================================== -->

            <section
                class="flex min-h-[500px] min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:min-h-0"
            >

                <!-- HEADER -->

                <div
                    class="flex shrink-0 items-center justify-between px-6 py-5"
                >
                    <div class="min-w-0">
                        <h2
                            class="text-lg font-semibold text-slate-800"
                        >
                            Status Loket
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Kondisi setiap loket.
                        </p>
                    </div>

                    <span
                        class="ml-4 shrink-0 rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600"
                    >
                        {{ dashboardData.counterStatus.length }}
                    </span>
                </div>


                <!-- COUNTER LIST -->

                <div
                    class="min-h-0 flex-1 overflow-y-auto px-4 py-4"
                >

                    <div
                        v-if="
                            dashboardData.counterStatus.length
                        "
                        class="space-y-3"
                    >

                        <div
                            v-for="counter in dashboardData.counterStatus"
                            :key="counter.id"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-white hover:shadow-sm"
                        >

                            <!-- HEADER -->

                            <div
                                class="flex items-start justify-between gap-3"
                            >

                                <div class="min-w-0">

                                    <div
                                        class="flex items-center gap-2"
                                    >

                                        <span
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200"
                                        >
                                            {{ counter.code }}
                                        </span>

                                        <div
                                            class="min-w-0"
                                        >
                                            <h3
                                                class="truncate text-sm font-bold text-slate-800"
                                            >
                                                {{ counter.name }}
                                            </h3>

                                            <p
                                                class="mt-0.5 truncate text-xs text-slate-500"
                                            >
                                                {{
                                                    counter.service?.name ??
                                                    "-"
                                                }}
                                            </p>
                                        </div>

                                    </div>

                                </div>


                                <!-- STATUS -->

                                <span
                                    :class="
                                        counterStatusClass(
                                            counter.status,
                                        )
                                    "
                                    class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold"
                                >
                                    {{
                                        counterStatusLabel(
                                            counter.status,
                                        )
                                    }}
                                </span>

                            </div>


                            <!-- STAFF ONLINE -->

                            <div
                                class="mt-3 flex items-center justify-between"
                            >
                                <span
                                    class="text-xs text-slate-400"
                                >
                                    Petugas online
                                </span>

                                <span
                                    class="text-xs font-semibold text-slate-600"
                                >
                                    {{
                                        counter.online_staff_count
                                    }}
                                    /
                                    {{
                                        counter.staff_count
                                    }}
                                </span>
                            </div>


                            <!-- ACTIVE QUEUE -->

                            <div
                                v-if="counter.queue"
                                class="mt-4 flex items-center justify-between rounded-xl bg-white px-4 py-3 ring-1 ring-slate-100"
                            >

                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Antrian
                                    </p>

                                    <p
                                        class="mt-1 text-2xl font-black tracking-wide text-indigo-700"
                                    >
                                        {{
                                            counter.queue.queue_number
                                        }}
                                    </p>
                                </div>


                                <div
                                    class="text-right"
                                >
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Panggilan
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-bold text-slate-700"
                                    >
                                        Ke-{{
                                            counter.queue.call_count
                                        }}
                                    </p>
                                </div>

                            </div>


                            <!-- EMPTY QUEUE -->

                            <div
                                v-else
                                class="mt-4 flex items-center justify-between rounded-xl bg-white px-4 py-3 ring-1 ring-slate-100"
                            >

                                <span
                                    class="text-xs font-medium text-slate-400"
                                >
                                    Tidak ada antrian
                                </span>

                                <span
                                    :class="
                                        counter.status ===
                                        'INACTIVE'
                                            ? 'bg-red-400'
                                            : 'bg-emerald-400'
                                    "
                                    class="h-2.5 w-2.5 rounded-full"
                                ></span>

                            </div>

                        </div>

                    </div>


                    <!-- EMPTY COUNTER -->

                    <div
                        v-else
                        class="flex min-h-full items-center justify-center"
                    >

                        <div class="text-center">

                            <div
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100"
                            >
                                <span
                                    class="text-xl text-slate-400"
                                >
                                    —
                                </span>
                            </div>

                            <p
                                class="mt-3 text-sm font-medium text-slate-600"
                            >
                                Belum ada loket.
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Loket yang tersedia akan muncul di sini.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->

                <div
                    class="flex h-[48px] shrink-0 items-center justify-between border-t border-slate-100 bg-slate-50 px-6"
                >

                    <span
                        class="text-xs text-slate-500"
                    >
                        Status seluruh loket
                    </span>

                    <span
                        class="text-xs font-semibold text-slate-600"
                    >
                        {{ dashboardData.counterStatus.length }}
                        loket
                    </span>

                </div>

            </section>

        </div>

    </AdminLayout>
</template>