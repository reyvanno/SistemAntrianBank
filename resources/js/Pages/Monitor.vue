<script setup>
import { Head } from "@inertiajs/vue3";
import { onMounted, onUnmounted, computed, ref } from "vue";

import MonitorNavbar from "@/Components/Layout/MonitorNavbar.vue";
import CounterCard from "@/Components/Monitor/CounterCard.vue";
import WaitingColumn from "@/Components/Monitor/WaitingColumn.vue";

const props = defineProps({
    counters: {
        type: Array,
        default: () => [],
    },

    waitingQueues: {
        type: Object,
        default: () => ({}),
    },

    latestCall: {
        type: Object,
        default: null,
    },
});

/*
|--------------------------------------------------------------------------
| Reactive Monitor Data
|--------------------------------------------------------------------------
*/

const counters = ref(props.counters);
const waitingQueues = ref(props.waitingQueues);
const latestCall = ref(props.latestCall);

/*
|--------------------------------------------------------------------------
| Voice State
|--------------------------------------------------------------------------
*/

const voiceEnabled = ref(false);
const isSpeaking = ref(false);

const lastAnnouncementKey = ref(
    props.latestCall ? createAnnouncementKey(props.latestCall) : null,
);

let pollingInterval = null;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Membuat identifier unik untuk setiap pemanggilan.
 *
 * Kenapa tidak hanya menggunakan queue ID?
 *
 * Karena:
 *
 * A006 CALL  -> id 20
 * A006 RECALL -> id 20
 *
 * ID sama.
 *
 * Tetapi called_at berubah.
 *
 * Jadi kita gunakan:
 *
 * queue_id + called_at
 */
function createAnnouncementKey(call) {
    if (!call) {
        return null;
    }

    return `${call.id}-${call.called_at}`;
}

/**
 * Mengubah angka menjadi bahasa Indonesia.
 */
function numberToIndonesian(number) {
    const numbers = [
        "nol",
        "satu",
        "dua",
        "tiga",
        "empat",
        "lima",
        "enam",
        "tujuh",
        "delapan",
        "sembilan",
    ];

    return String(number)
        .split("")
        .map((digit) => numbers[Number(digit)])
        .join(" ");
}

/**
 * Mengubah nomor antrian menjadi format
 * yang lebih mudah dibaca oleh TTS.
 *
 * Contoh:
 *
 * A006
 * ↓
 * A nol nol enam
 */
function formatQueueNumber(queueNumber) {
    if (!queueNumber) {
        return "";
    }

    const prefix = queueNumber.match(/^[A-Za-z]+/)?.[0] ?? "";

    const number = queueNumber.match(/\d+$/)?.[0] ?? "";

    const prefixText = prefix.toUpperCase().split("").join(" ");

    const numberText = numberToIndonesian(number);

    return `${prefixText} ${numberText}`;
}

/**
 * Mengubah kode loket menjadi suara.
 *
 * T1
 * ↓
 * satu
 *
 * CS2
 * ↓
 * dua
 */
function formatCounterNumber(counterCode) {
    if (!counterCode) {
        return "";
    }

    const number = counterCode.match(/\d+$/)?.[0] ?? "";

    return numberToIndonesian(number);
}

/**
 * Membuat kalimat pemanggilan.
 */
function createAnnouncementText(call) {
    const queueNumber = formatQueueNumber(call.queue_number);

    const counterNumber = formatCounterNumber(call.counter);

    return `Nomor antrian, ${queueNumber}, silakan menuju loket ${counterNumber}.`;
}

/*
|--------------------------------------------------------------------------
| Text To Speech
|--------------------------------------------------------------------------
*/

function speakQueue(call) {
    if (!call) {
        return;
    }

    /*
     * Browser tidak mendukung Speech Synthesis.
     */
    if (!("speechSynthesis" in window)) {
        console.warn("Browser tidak mendukung Speech Synthesis.");

        return;
    }

    /*
     * Suara belum diaktifkan oleh user.
     */
    if (!voiceEnabled.value) {
        return;
    }

    /*
     * Hentikan suara sebelumnya jika masih berjalan.
     */
    window.speechSynthesis.cancel();

    const text = createAnnouncementText(call);

    const utterance = new SpeechSynthesisUtterance(text);

    /*
     * Bahasa Indonesia.
     */
    utterance.lang = "id-ID";

    /*
     * Kecepatan suara.
     *
     * 1 = normal
     *
     * Sedikit diperlambat supaya nomor
     * lebih mudah didengar.
     */
    utterance.rate = 0.85;

    /*
     * Tinggi rendah suara.
     */
    utterance.pitch = 1;

    /*
     * Volume.
     */
    utterance.volume = 1;

    utterance.onstart = () => {
        isSpeaking.value = true;
    };

    utterance.onend = () => {
        isSpeaking.value = false;
    };

    utterance.onerror = () => {
        isSpeaking.value = false;
    };

    window.speechSynthesis.speak(utterance);
}

/*
|--------------------------------------------------------------------------
| Aktifkan Suara
|--------------------------------------------------------------------------
*/

function enableVoice() {
    if (!("speechSynthesis" in window)) {
        alert("Browser Anda tidak mendukung fitur suara.");

        return;
    }

    /*
     * Tandai suara telah diaktifkan.
     */
    voiceEnabled.value = true;

    /*
     * Kita lakukan speech kecil untuk membuka
     * izin audio browser.
     *
     * Kalimat ini sengaja pendek.
     */
    const utterance = new SpeechSynthesisUtterance("Suara monitor aktif.");

    utterance.lang = "id-ID";
    utterance.rate = 0.9;
    utterance.volume = 1;

    window.speechSynthesis.cancel();

    window.speechSynthesis.speak(utterance);
}

/*
|--------------------------------------------------------------------------
| Polling Monitor
|--------------------------------------------------------------------------
*/

/**
 * Mengambil data Monitor terbaru.
 *
 * Untuk tahap pertama kita gunakan polling.
 *
 * Jadi:
 *
 * Monitor
 * ↓
 * setiap 1.5 detik
 * ↓
 * /monitor/data
 */
async function fetchMonitorData() {
    try {
        const response = await fetch(route("monitor.data"), {
            method: "GET",

            headers: {
                Accept: "application/json",
            },

            cache: "no-store",
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        /*
         * Update data counter.
         */
        counters.value = data.counters ?? [];

        /*
         * Update antrian waiting.
         */
        waitingQueues.value = data.waitingQueues ?? {};

        /*
         * Update latest call.
         */
        const newLatestCall = data.latestCall ?? null;

        latestCall.value = newLatestCall;

        /*
         * Cek apakah ada pemanggilan baru.
         */
        if (newLatestCall) {
            const newKey = createAnnouncementKey(newLatestCall);

            /*
             * Kalau key berbeda berarti:
             *
             * CALL baru
             * atau
             * RECALL
             */
            if (newKey && newKey !== lastAnnouncementKey.value) {
                lastAnnouncementKey.value = newKey;

                /*
                 * Mainkan suara.
                 */
                speakQueue(newLatestCall);
            }
        }
    } catch (error) {
        console.error("Gagal mengambil data monitor:", error);
    }
}

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const totalWaiting = computed(() =>
    Object.values(waitingQueues.value).reduce(
        (total, queues) => total + queues.length,
        0,
    ),
);

const serviceColor = (service) => {
    switch (service) {
        case "Teller":
            return "blue";

        case "Customer Service":
            return "emerald";

        default:
            return "amber";
    }
};

const counterStatus = (counter) => {
    if (!counter.queues?.length) {
        return "offline";
    }

    switch (counter.queues[0].status) {
        case "CALLED":
            return "called";

        case "SERVING":
            return "processing";

        default:
            return "waiting";
    }
};

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    /*
     * Fullscreen.
     *
     * Browser mungkin menolak request ini
     * karena fullscreen juga membutuhkan
     * user interaction.
     *
     * Jadi kita tangkap error-nya supaya
     * tidak mengganggu monitor.
     */
    document.documentElement.requestFullscreen?.()?.catch?.(() => {});

    /*
     * Poll pertama.
     */
    fetchMonitorData();

    /*
     * Poll setiap 1.5 detik.
     */
    pollingInterval = setInterval(fetchMonitorData, 1500);
});

onUnmounted(() => {
    /*
     * Hentikan polling.
     */
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }

    /*
     * Hentikan suara jika halaman ditutup.
     */
    if ("speechSynthesis" in window) {
        window.speechSynthesis.cancel();
    }
});
</script>

<template>
    <Head title="Monitor" />

    <div class="flex h-screen flex-col overflow-hidden bg-slate-100">
        <MonitorNavbar />

        <!--
        |--------------------------------------------------------------------------
        | Voice Control
        |--------------------------------------------------------------------------
        -->

        <div
            class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-2"
        >
            <div class="flex items-center gap-3">
                <div
                    :class="
                        voiceEnabled
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-amber-100 text-amber-700'
                    "
                    class="flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold"
                >
                    <span class="h-2.5 w-2.5 rounded-full bg-current"></span>

                    {{ voiceEnabled ? "Suara Aktif" : "Suara Belum Aktif" }}
                </div>

                <span
                    v-if="isSpeaking"
                    class="text-sm font-medium text-slate-500"
                >
                    Sedang memanggil...
                </span>
            </div>

            <button
                v-if="!voiceEnabled"
                type="button"
                @click="enableVoice"
                class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 active:scale-95"
            >
                🔊 Aktifkan Suara
            </button>

            <span v-else class="text-xs text-slate-400">
                Sistem suara siap digunakan
            </span>
        </div>

        <main
            class="grid flex-1 grid-rows-[auto_240px_minmax(0,1fr)] gap-4 overflow-hidden p-4"
        >
            <!-- Header -->

            <div class="flex items-center justify-between">
                <h2
                    class="text-2xl font-semibold tracking-tight text-slate-800"
                >
                    Sedang Dipanggil
                </h2>

                <span
                    class="rounded-full bg-slate-200 px-4 py-2 text-sm font-medium text-slate-600"
                >
                    {{ counters.length }}
                    Loket
                </span>
            </div>

            <!-- Counter -->

            <div class="grid grid-cols-5 gap-5">
                <CounterCard
                    v-for="counter in counters"
                    :key="counter.id"
                    :counter="counter.name"
                    :queue="counter.queues[0]?.queue_number"
                    :service="counter.service?.name"
                    :status="counterStatus(counter)"
                />
            </div>

            <!-- Waiting -->

            <div
                class="flex min-h-0 flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">
                        ANTRIAN MENUNGGU
                    </h2>

                    <span
                        class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700"
                    >
                        Total :
                        {{ totalWaiting }}
                        Antrian
                    </span>
                </div>

                <div class="grid min-h-0 flex-1 grid-cols-3 gap-5">
                    <WaitingColumn
                        v-for="(queues, service) in waitingQueues"
                        :key="service"
                        :title="service.toUpperCase()"
                        :color="serviceColor(service)"
                        :queues="queues.map((queue) => queue.queue_number)"
                    />
                </div>
            </div>
        </main>

        <footer
            class="relative h-10 shrink-0 overflow-hidden bg-indigo-700 text-white"
        >
            <div
                class="ticker absolute flex h-full items-center whitespace-nowrap text-sm font-medium"
            >
                <span class="mx-16">
                    Selamat Datang di Sistem Antrian Bank
                </span>

                <span class="mx-16">
                    Harap memperhatikan nomor antrian yang sedang dipanggil
                </span>

                <span class="mx-16">
                    Siapkan dokumen sebelum menuju loket
                </span>

                <span class="mx-16">
                    Nasabah Prioritas akan dilayani sesuai ketentuan bank
                </span>

                <span class="mx-16"> Terima kasih atas kunjungan Anda </span>

                <span class="mx-16">
                    Selamat Datang di Sistem Antrian Bank
                </span>

                <span class="mx-16">
                    Harap memperhatikan nomor antrian yang sedang dipanggil
                </span>

                <span class="mx-16">
                    Siapkan dokumen sebelum menuju loket
                </span>

                <span class="mx-16">
                    Nasabah Prioritas akan dilayani sesuai ketentuan bank
                </span>

                <span class="mx-16"> Terima kasih atas kunjungan Anda </span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.ticker {
    animation: ticker 35s linear infinite;
}

@keyframes ticker {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-50%);
    }
}
</style>
