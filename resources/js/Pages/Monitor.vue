<script setup>
import { Head } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";

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
| REACTIVE MONITOR DATA
|--------------------------------------------------------------------------
*/

const counters = ref(props.counters);
const waitingQueues = ref(props.waitingQueues);
const latestCall = ref(props.latestCall);

/*
|--------------------------------------------------------------------------
| VOICE
|--------------------------------------------------------------------------
*/

const voiceEnabled = ref(false);
const isSpeaking = ref(false);

/*
 * Audio notifikasi.
 *
 * File:
 *
 * public/sounds/notification.mp3
 *
 * sehingga browser mengaksesnya melalui:
 *
 * /sounds/notification.mp3
 */
const notificationSound = new Audio(
    "/sounds/notification.mp3",
);

notificationSound.preload = "auto";

/*
|--------------------------------------------------------------------------
| ANNOUNCEMENT STATE
|--------------------------------------------------------------------------
*/

const lastAnnouncementKey = ref(
    props.latestCall
        ? createAnnouncementKey(props.latestCall)
        : null,
);

let pollingInterval = null;

/*
 * Mencegah dua pemanggilan
 * berjalan bersamaan.
 */
let announcementQueue = [];

let isAnnouncementPlaying = false;

/*
|--------------------------------------------------------------------------
| ANNOUNCEMENT KEY
|--------------------------------------------------------------------------
*/

function createAnnouncementKey(call) {
    if (!call) {
        return null;
    }

    return `${call.id}-${call.called_at}`;
}

/*
|--------------------------------------------------------------------------
| NUMBER TO INDONESIAN
|--------------------------------------------------------------------------
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

/*
|--------------------------------------------------------------------------
| FORMAT QUEUE NUMBER
|--------------------------------------------------------------------------
*/

function formatQueueNumber(queueNumber) {
    if (!queueNumber) {
        return "";
    }

    const prefix =
        queueNumber.match(/^[A-Za-z]+/)?.[0] ?? "";

    const number =
        queueNumber.match(/\d+$/)?.[0] ?? "";

    const prefixText = prefix
        .toUpperCase()
        .split("")
        .join(", ");

    const numberText = number
        .split("")
        .map((digit) => {
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

            return numbers[Number(digit)];
        })
        .join(", ");

    return `${prefixText}, ${numberText}`;
}

/*
|--------------------------------------------------------------------------
| FORMAT COUNTER NUMBER
|--------------------------------------------------------------------------
*/

function formatCounterNumber(counterCode) {
    if (!counterCode) {
        return "";
    }

    const number =
        counterCode.match(/\d+$/)?.[0] ?? "";

    return number
        .split("")
        .map((digit) => {
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

            return numbers[Number(digit)];
        })
        .join(", ");
}

/*
|--------------------------------------------------------------------------
| ANNOUNCEMENT TEXT
|--------------------------------------------------------------------------
*/

function createAnnouncementText(call) {
    const queueNumber =
        formatQueueNumber(call.queue_number);

    const counterNumber =
        formatCounterNumber(call.counter);

    return `Nomor antrian, ${queueNumber}. Silakan menuju loket ${counterNumber}.`;
}

/*
|--------------------------------------------------------------------------
| PLAY NOTIFICATION SOUND
|--------------------------------------------------------------------------
|
| Urutan:
|
| notification.mp3
|       ↓
| selesai
|       ↓
| TTS
|
*/

function playNotificationSound() {
    return new Promise((resolve) => {
        /*
         * Pastikan audio berhenti
         * dari pemanggilan sebelumnya.
         */
        notificationSound.pause();

        notificationSound.currentTime = 0;

        /*
         * Ketika audio selesai,
         * lanjut ke TTS.
         */
        const handleEnded = () => {
            cleanup();

            resolve();
        };

        /*
         * Kalau audio error,
         * jangan sampai TTS ikut macet.
         */
        const handleError = () => {
            console.warn(
                "Gagal memainkan suara notifikasi.",
            );

            cleanup();

            resolve();
        };

        const cleanup = () => {
            notificationSound.removeEventListener(
                "ended",
                handleEnded,
            );

            notificationSound.removeEventListener(
                "error",
                handleError,
            );
        };

        notificationSound.addEventListener(
            "ended",
            handleEnded,
        );

        notificationSound.addEventListener(
            "error",
            handleError,
        );

        const playPromise =
            notificationSound.play();

        /*
         * Browser dapat menolak audio
         * apabila belum ada user interaction.
         */
        if (playPromise !== undefined) {
            playPromise.catch((error) => {
                console.warn(
                    "Audio notification diblokir browser:",
                    error,
                );

                cleanup();

                resolve();
            });
        }
    });
}

/*
|--------------------------------------------------------------------------
| SPEECH
|--------------------------------------------------------------------------
*/

function speakQueue(call) {
    return new Promise((resolve) => {
        if (!call) {
            resolve();

            return;
        }

        if (
            !("speechSynthesis" in window)
        ) {
            console.warn(
                "Browser tidak mendukung Speech Synthesis.",
            );

            resolve();

            return;
        }

        if (!voiceEnabled.value) {
            resolve();

            return;
        }

        /*
         * Hentikan speech sebelumnya.
         */
        window.speechSynthesis.cancel();

        const text =
            createAnnouncementText(call);

        const utterance =
            new SpeechSynthesisUtterance(
                text,
            );

        utterance.lang = "id-ID";

        /*
         * Sedikit diperlambat agar
         * nomor lebih jelas.
         */
        utterance.rate = 0.85;

        utterance.pitch = 1;

        utterance.volume = 1;

        utterance.onstart = () => {
            isSpeaking.value = true;
        };

        utterance.onend = () => {
            isSpeaking.value = false;

            resolve();
        };

        utterance.onerror = () => {
            isSpeaking.value = false;

            resolve();
        };

        window.speechSynthesis.speak(
            utterance,
        );
    });
}

/*
|--------------------------------------------------------------------------
| PLAY ANNOUNCEMENT
|--------------------------------------------------------------------------
|
| Satu pemanggilan:
|
| DING DING DING
|       ↓
| TTS
|
*/

async function playAnnouncement(call) {
    if (!call) {
        return;
    }

    /*
     * Kalau suara belum diaktifkan,
     * jangan memainkan apapun.
     */
    if (!voiceEnabled.value) {
        return;
    }

    /*
     * Pastikan hanya satu announcement
     * berjalan dalam satu waktu.
     */
    announcementQueue.push(call);

    if (isAnnouncementPlaying) {
        return;
    }

    isAnnouncementPlaying = true;

    while (announcementQueue.length > 0) {
        const currentCall =
            announcementQueue.shift();

        /*
         * 1. DING DING DING
         */
        await playNotificationSound();

        /*
         * 2. TTS
         */
        await speakQueue(currentCall);
    }

    isAnnouncementPlaying = false;
}

/*
|--------------------------------------------------------------------------
| ENABLE VOICE
|--------------------------------------------------------------------------
*/

function enableVoice() {
    if (
        !("speechSynthesis" in window)
    ) {
        alert(
            "Browser Anda tidak mendukung fitur suara.",
        );

        return;
    }

    voiceEnabled.value = true;

    /*
     * Reset audio.
     */
    notificationSound.pause();

    notificationSound.currentTime = 0;

    /*
     * Suara pembuka.
     */
    const utterance =
        new SpeechSynthesisUtterance(
            "Suara monitor aktif.",
        );

    utterance.lang = "id-ID";

    utterance.rate = 0.9;

    utterance.volume = 1;

    window.speechSynthesis.cancel();

    window.speechSynthesis.speak(
        utterance,
    );
}

/*
|--------------------------------------------------------------------------
| MONITOR POLLING
|--------------------------------------------------------------------------
*/

async function fetchMonitorData() {
    try {
        const response = await fetch(
            route("monitor.data"),
            {
                method: "GET",

                headers: {
                    Accept:
                        "application/json",
                },

                cache: "no-store",
            },
        );

        if (!response.ok) {
            throw new Error(
                `HTTP ${response.status}`,
            );
        }

        const data =
            await response.json();

        /*
         * COUNTERS
         */
        counters.value =
            data.counters ?? [];

        /*
         * WAITING QUEUES
         */
        waitingQueues.value =
            data.waitingQueues ?? {};

        /*
         * LATEST CALL
         */
        const newLatestCall =
            data.latestCall ?? null;

        latestCall.value =
            newLatestCall;

        /*
         * CEK PEMANGGILAN BARU
         */
        if (newLatestCall) {
            const newKey =
                createAnnouncementKey(
                    newLatestCall,
                );

            /*
             * Key berubah berarti:
             *
             * CALL baru
             * atau
             * RECALL
             */
            if (
                newKey &&
                newKey !==
                    lastAnnouncementKey.value
            ) {
                lastAnnouncementKey.value =
                    newKey;

                /*
                 * Masukkan ke queue
                 * announcement.
                 */
                playAnnouncement(
                    newLatestCall,
                );
            }
        }
    } catch (error) {
        console.error(
            "Gagal mengambil data monitor:",
            error,
        );
    }
}

/*
|--------------------------------------------------------------------------
| WAITING QUEUES
|--------------------------------------------------------------------------
*/

const totalWaiting = computed(() =>
    Object.values(
        waitingQueues.value,
    ).reduce(
        (total, queues) =>
            total + queues.length,
        0,
    ),
);

const tellerQueues = computed(() => {
    return (
        waitingQueues.value[
            "Teller"
        ] ?? []
    );
});

const customerServiceQueues =
    computed(() => {
        return (
            waitingQueues.value[
                "Customer Service"
            ] ?? []
        );
    });

/*
|--------------------------------------------------------------------------
| COUNTER STATUS
|--------------------------------------------------------------------------
*/

const counterStatus = (counter) => {
    switch (counter.status) {
        case "AVAILABLE":
            return "available";

        case "CALLED":
            return "called";

        case "SERVING":
            return "processing";

        case "INACTIVE":
        default:
            return "offline";
    }
};

/*
|--------------------------------------------------------------------------
| LIFECYCLE
|--------------------------------------------------------------------------
*/

onMounted(() => {
    /*
     * Fullscreen.
     */
    document.documentElement
        .requestFullscreen?.()
        ?.catch?.(() => {});

    /*
     * Fetch pertama.
     */
    fetchMonitorData();

    /*
     * Polling setiap 1.5 detik.
     */
    pollingInterval =
        setInterval(
            fetchMonitorData,
            1500,
        );
});

onUnmounted(() => {
    /*
     * Stop polling.
     */
    if (pollingInterval) {
        clearInterval(
            pollingInterval,
        );

        pollingInterval = null;
    }

    /*
     * Stop audio notification.
     */
    notificationSound.pause();

    notificationSound.currentTime = 0;

    /*
     * Stop TTS.
     */
    if (
        "speechSynthesis" in window
    ) {
        window.speechSynthesis.cancel();
    }

    /*
     * Bersihkan queue announcement.
     */
    announcementQueue = [];

    isAnnouncementPlaying = false;
});
</script>

<template>
    <Head title="Monitor" />

    <div
        class="flex h-screen flex-col overflow-hidden bg-slate-100"
    >
        <!-- =========================================================
             NAVBAR
        ========================================================== -->

        <MonitorNavbar />

        <!-- =========================================================
             VOICE CONTROL
        ========================================================== -->

        <div
            class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-2"
        >
            <div
                class="flex items-center gap-3"
            >
                <div
                    :class="
                        voiceEnabled
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-amber-100 text-amber-700'
                    "
                    class="flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold"
                >
                    <span
                        class="h-2.5 w-2.5 rounded-full bg-current"
                    ></span>

                    {{
                        voiceEnabled
                            ? "Suara Aktif"
                            : "Suara Belum Aktif"
                    }}
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

            <span
                v-else
                class="text-xs text-slate-400"
            >
                Sistem suara siap digunakan
            </span>
        </div>

        <!-- =========================================================
             MAIN
        ========================================================== -->

        <main
            class="grid min-h-0 flex-1 grid-rows-[auto_240px_minmax(0,1fr)] gap-4 overflow-hidden p-4"
        >
            <!-- HEADER -->

            <div
                class="flex items-center justify-between"
            >
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

            <!-- COUNTERS -->

            <div
                class="grid min-h-0 grid-cols-5 gap-5"
            >
                <CounterCard
                    v-for="counter in counters"
                    :key="counter.id"
                    :counter="counter.name"
                    :queue="
                        counter.queue
                            ?.queue_number
                    "
                    :service="
                        counter.service
                            ?.name
                    "
                    :status="
                        counterStatus(
                            counter,
                        )
                    "
                    :online-staff-count="
                        counter.online_staff_count
                    "
                    :staff-count="
                        counter.staff_count
                    "
                />
            </div>

            <!-- =====================================================
                 WAITING QUEUES
            ====================================================== -->

            <div
                class="flex min-h-0 min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <!-- HEADER -->

                <div
                    class="mb-5 flex shrink-0 items-center justify-between"
                >
                    <h2
                        class="text-lg font-semibold text-slate-800"
                    >
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

                <!-- TELLER + CUSTOMER SERVICE -->

                <div
                    class="grid min-h-0 flex-1 grid-cols-1 gap-5 md:grid-cols-2"
                >
                    <!-- TELLER -->

                    <WaitingColumn
                        title="TELLER"
                        color="blue"
                        :queues="
                            tellerQueues.map(
                                (queue) =>
                                    queue.queue_number,
                            )
                        "
                    />

                    <!-- CUSTOMER SERVICE -->

                    <WaitingColumn
                        title="CUSTOMER SERVICE"
                        color="emerald"
                        :queues="
                            customerServiceQueues.map(
                                (queue) =>
                                    queue.queue_number,
                            )
                        "
                    />
                </div>
            </div>
        </main>

        <!-- =========================================================
             FOOTER
        ========================================================== -->

        <footer
            class="relative h-10 shrink-0 overflow-hidden bg-indigo-700 text-white"
        >
            <div
                class="ticker absolute flex h-full items-center whitespace-nowrap text-sm font-medium"
            >
                <span
                    class="mx-16"
                >
                    Selamat Datang di Sistem Antrian Bank
                </span>

                <span
                    class="mx-16"
                >
                    Harap memperhatikan nomor antrian yang sedang dipanggil
                </span>

                <span
                    class="mx-16"
                >
                    Siapkan dokumen sebelum menuju loket
                </span>

                <span
                    class="mx-16"
                >
                    Nasabah Prioritas akan dilayani sesuai ketentuan bank
                </span>

                <span
                    class="mx-16"
                >
                    Terima kasih atas kunjungan Anda
                </span>

                <span
                    class="mx-16"
                >
                    Selamat Datang di Sistem Antrian Bank
                </span>

                <span
                    class="mx-16"
                >
                    Harap memperhatikan nomor antrian yang sedang dipanggil
                </span>

                <span
                    class="mx-16"
                >
                    Siapkan dokumen sebelum menuju loket
                </span>

                <span
                    class="mx-16"
                >
                    Nasabah Prioritas akan dilayani sesuai ketentuan bank
                </span>

                <span
                    class="mx-16"
                >
                    Terima kasih atas kunjungan Anda
                </span>
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