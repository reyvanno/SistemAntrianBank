<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";

import {
    Head,
    router,
    useForm,
    usePage,
} from "@inertiajs/vue3";

import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";

import SearchBox from "@/Components/Shared/SearchBox.vue";
import DataTable from "@/Components/Shared/DataTable.vue";
import Pagination from "@/Components/Shared/Pagination.vue";

import {
    TicketIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

import { can } from "@/lib/can";

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| AUTH
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
| REACTIVE QUEUES
|--------------------------------------------------------------------------
|
| Kita membuat salinan reactive.
|
| Jangan langsung menggunakan props.queues
| karena data akan diperbarui oleh polling.
|
*/

const queueData = ref({
    ...props.queues,
    data: [...(props.queues.data ?? [])],
    links: [...(props.queues.links ?? [])],
});

/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

const search = ref(
    props.filters.search ?? ""
);

/*
|--------------------------------------------------------------------------
| SEARCH TIMER
|--------------------------------------------------------------------------
*/

let searchTimer = null;

watch(
    search,
    (value) => {
        /*
         * Jangan langsung request setiap karakter.
         *
         * Contoh user mengetik:
         *
         * A
         * A0
         * A00
         * A001
         *
         * Kita tunggu sebentar.
         */
        if (searchTimer) {
            clearTimeout(searchTimer);
        }

        searchTimer = window.setTimeout(() => {
            router.get(
                route("admin.queues.index"),
                {
                    search: value || undefined,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                }
            );
        }, 300);
    }
);

/*
|--------------------------------------------------------------------------
| CREATE QUEUE
|--------------------------------------------------------------------------
*/

const form = useForm({
    service_id: "",
});

const submit = () => {
    if (
        !form.service_id ||
        form.processing
    ) {
        return;
    }

    form.post(
        route("admin.queues.store"),
        {
            preserveScroll: true,

            onSuccess: () => {
                form.reset();

                /*
                 * Tidak perlu reload manual.
                 *
                 * Polling akan mengambil queue baru
                 * dalam maksimal ±1.5 detik.
                 */
            },
        }
    );
};

/*
|--------------------------------------------------------------------------
| CANCEL QUEUE
|--------------------------------------------------------------------------
*/

const cancelQueue = (queue) => {
    /*
     * Hanya WAITING yang dapat dibatalkan.
     */
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

            /*
             * Polling akan mengambil status
             * terbaru setelah request selesai.
             */
            onFinish: () => {
                refreshQueues();
            },
        }
    );
};

/*
|--------------------------------------------------------------------------
| STATUS BADGE
|--------------------------------------------------------------------------
*/

const badge = (status) => {
    switch (status) {
        case "WAITING":
            return "bg-yellow-100 text-yellow-700";

        case "CALLED":
            return "bg-blue-100 text-blue-700";

        case "SERVING":
            return "bg-emerald-100 text-emerald-700";

        case "FINISHED":
            return "bg-violet-100 text-violet-700";

        case "SKIPPED":
            return "bg-orange-100 text-orange-700";

        case "CANCELLED":
            return "bg-red-100 text-red-700";

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

/*
|--------------------------------------------------------------------------
| REALTIME QUEUE
|--------------------------------------------------------------------------
*/

let queueTimer = null;

let requestInProgress = false;

let pageActive = true;

/**
 * Mengambil nomor halaman saat ini.
 *
 * Contoh:
 *
 * /admin/queues?page=2&search=A
 *
 * maka:
 *
 * page = 2
 * search = A
 */
const getCurrentPage = () => {
    const params = new URLSearchParams(
        window.location.search
    );

    return params.get("page") ?? "1";
};

/**
 * Mengambil search terbaru.
 */
const getCurrentSearch = () => {
    /*
     * Prioritaskan value reactive.
     *
     * Ini penting ketika user sedang
     * berada pada halaman dengan pencarian.
     */
    return search.value || "";
};

/**
 * Mengambil data queue terbaru.
 *
 * Ini BUKAN Inertia reload.
 *
 * Ini request JSON biasa.
 */
const refreshQueues = async () => {
    /*
     * Jangan request jika halaman sudah ditutup.
     */
    if (!pageActive) {
        return;
    }

    /*
     * Jangan menumpuk request.
     *
     * Kalau request sebelumnya belum selesai,
     * tunggu polling berikutnya.
     */
    if (requestInProgress) {
        return;
    }

    requestInProgress = true;

    try {
        const params = new URLSearchParams();

        const currentPage =
            getCurrentPage();

        const currentSearch =
            getCurrentSearch();

        /*
         * Pagination.
         */
        if (currentPage) {
            params.set(
                "page",
                currentPage
            );
        }

        /*
         * Search.
         */
        if (currentSearch) {
            params.set(
                "search",
                currentSearch
            );
        }

        const url =
            route("admin.queues.data") +
            (params.toString()
                ? `?${params.toString()}`
                : "");

        const response = await fetch(
            url,
            {
                method: "GET",

                headers: {
                    Accept:
                        "application/json",

                    "X-Requested-With":
                        "XMLHttpRequest",
                },

                cache: "no-store",
            }
        );

        if (!response.ok) {
            throw new Error(
                `HTTP ${response.status}`
            );
        }

        const result =
            await response.json();

        /*
         * Update queue data.
         *
         * Vue akan otomatis
         * merender ulang tabel.
         */
        if (result.queues) {
            queueData.value = {
                ...result.queues,

                data: [
                    ...(result.queues.data ?? []),
                ],

                links: [
                    ...(result.queues.links ?? []),
                ],
            };
        }
    } catch (error) {
        /*
         * Jangan membuat halaman error
         * hanya karena satu polling gagal.
         */
        console.error(
            "Gagal memperbarui data antrian:",
            error
        );
    } finally {
        requestInProgress = false;
    }
};

/**
 * Memulai polling.
 */
const startQueuePolling = () => {
    /*
     * Bersihkan timer lama jika ada.
     */
    if (queueTimer) {
        clearInterval(queueTimer);
    }

    /*
     * Poll setiap 1.5 detik.
     */
    queueTimer =
        window.setInterval(
            refreshQueues,
            1500
        );
};

/**
 * Menghentikan polling.
 */
const stopQueuePolling = () => {
    if (queueTimer) {
        clearInterval(queueTimer);

        queueTimer = null;
    }
};

/*
|--------------------------------------------------------------------------
| LIFECYCLE
|--------------------------------------------------------------------------
*/

onMounted(() => {
    pageActive = true;

    /*
     * Ambil data terbaru saat halaman pertama kali
     * selesai dimuat.
     */
    refreshQueues();

    /*
     * Mulai realtime polling.
     */
    startQueuePolling();
});

onBeforeUnmount(() => {
    pageActive = false;

    /*
     * Hentikan polling.
     */
    stopQueuePolling();

    /*
     * Hentikan debounce search.
     */
    if (searchTimer) {
        clearTimeout(searchTimer);

        searchTimer = null;
    }
});
</script>

<template>
    <Head title="Antrian" />

    <AdminLayout>

        <!-- =========================================================
             HEADER
        ========================================================== -->

        <div class="mb-8">

            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
            >

                <div>
                    <h1
                        class="text-2xl font-bold text-slate-800"
                    >
                        Antrian
                    </h1>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        {{
                            isAdmin
                                ? "Kelola dan pantau seluruh nomor antrian."
                                : "Kelola antrian pada layanan Anda."
                        }}
                    </p>
                </div>

                <div
                    class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600"
                >
                    {{ queueData.total }}
                    Antrian
                </div>

            </div>

        </div>


        <!-- =========================================================
             CREATE QUEUE
        ========================================================== -->

        <div
            v-if="can('queue.create')"
            class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >

            <div
                class="mb-5 flex items-center gap-3"
            >

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50"
                >
                    <TicketIcon
                        class="h-6 w-6 text-indigo-600"
                    />
                </div>

                <div>
                    <h2
                        class="text-lg font-bold text-slate-800"
                    >
                        Ambil Nomor Antrian
                    </h2>

                    <p
                        class="text-sm text-slate-500"
                    >
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
                        {{ service.code }}
                        -
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


        <!-- =========================================================
             SEARCH
        ========================================================== -->

        <div class="mb-5">
            <SearchBox
                v-model="search"
                placeholder="Cari nomor antrian..."
            />
        </div>


        <!-- =========================================================
             TABLE
        ========================================================== -->

        <div
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >

            <div class="mb-5">

                <div
                    class="flex items-center justify-between gap-4"
                >

                    <div>
                        <h2
                            class="text-lg font-semibold text-slate-800"
                        >
                            Daftar Antrian
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{
                                isAdmin
                                    ? "Riwayat dan status seluruh nomor antrian."
                                    : "Riwayat antrian pada layanan Anda."
                            }}
                        </p>
                    </div>

                    <!-- REALTIME INDICATOR -->

                    <div
                        class="hidden items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 sm:flex"
                    >
                        <span
                            class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                        ></span>

                        Live
                    </div>

                </div>

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

                <!-- =================================================
                     QUEUE
                ================================================== -->

                <tr
                    v-for="queue in queueData.data"
                    :key="queue.id"
                    class="border-b border-slate-100 last:border-0 hover:bg-slate-50"
                >

                    <!-- NUMBER -->

                    <td
                        class="px-4 py-4 text-center"
                    >
                        <span
                            class="font-bold text-slate-800"
                        >
                            {{ queue.queue_number }}
                        </span>
                    </td>


                    <!-- SERVICE -->

                    <td
                        class="px-4 py-4 text-center"
                    >

                        <div
                            class="font-medium text-slate-700"
                        >
                            {{ queue.service?.name ?? "-" }}
                        </div>

                        <div
                            class="text-xs text-slate-400"
                        >
                            {{ queue.service?.code ?? "" }}
                        </div>

                    </td>


                    <!-- COUNTER -->

                    <td
                        class="px-4 py-4 text-center text-slate-600"
                    >
                        {{ queue.counter?.code ?? "-" }}
                    </td>


                    <!-- STATUS -->

                    <td
                        class="px-4 py-4 text-center"
                    >

                        <span
                            :class="badge(queue.status)"
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold transition-all duration-300"
                        >
                            {{ statusLabel(queue.status) }}
                        </span>

                    </td>


                    <!-- USER -->

                    <td
                        class="px-4 py-4 text-center text-slate-600"
                    >
                        {{ queue.handled_by?.name ?? "-" }}
                    </td>


                    <!-- ACTION -->

                    <td
                        class="px-4 py-4 text-center"
                    >

                        <button
                            v-if="
                                can('queue.cancel') &&
                                queue.status === 'WAITING'
                            "
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                            @click="cancelQueue(queue)"
                        >

                            <XMarkIcon
                                class="h-4 w-4"
                            />

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


                <!-- =================================================
                     EMPTY
                ================================================== -->

                <tr
                    v-if="queueData.data.length === 0"
                >

                    <td
                        colspan="6"
                        class="px-4 py-12 text-center"
                    >

                        <p
                            class="font-medium text-slate-600"
                        >
                            Tidak ada data antrian.
                        </p>

                        <p
                            class="mt-1 text-sm text-slate-400"
                        >
                            Belum ada antrian yang sesuai dengan pencarian.
                        </p>

                    </td>

                </tr>

            </DataTable>

        </div>


        <!-- =========================================================
             PAGINATION
        ========================================================== -->

        <div class="mt-6">
            <Pagination
                :links="queueData.links"
            />
        </div>

    </AdminLayout>
</template>