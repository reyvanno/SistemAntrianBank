<script setup>
import {
    ArrowPathIcon,
    CheckCircleIcon,
    ForwardIcon,
    PhoneIcon,
    PlayIcon,
} from "@heroicons/vue/24/outline";
import { computed } from "vue";
import { can } from "@/lib/can.js";

const props = defineProps({
    counter: {
        type: Object,
        required: true,
    },

    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "call",
    "recall",
    "start",
    "finish",
    "skip",
]);

const statusConfig = computed(() => {
    switch (props.counter.status) {
        case "AVAILABLE":
            return {
                label: "Tersedia",
                badge: "bg-emerald-100 text-emerald-700",
                dot: "bg-emerald-500",
            };

        case "CALLED":
            return {
                label: "Antrian Dipanggil",
                badge: "bg-blue-100 text-blue-700",
                dot: "bg-blue-500",
            };

        case "SERVING":
            return {
                label: "Sedang Melayani",
                badge: "bg-amber-100 text-amber-700",
                dot: "bg-amber-500",
            };

        case "INACTIVE":
            return {
                label: "Tidak Aktif",
                badge: "bg-red-100 text-red-700",
                dot: "bg-red-500",
            };

        default:
            return {
                label: props.counter.status,
                badge: "bg-slate-100 text-slate-700",
                dot: "bg-slate-500",
            };
    }
});

const queueStatusLabel = computed(() => {
    switch (props.counter.queue?.status) {
        case "CALLED":
            return "Menunggu Dilayani";

        case "SERVING":
            return "Sedang Dilayani";

        default:
            return "";
    }
});

const canCall = computed(() => {
    return (
        can("queue.call") &&
        props.counter.status === "AVAILABLE"
    );
});

const canRecall = computed(() => {
    return (
        can("queue.recall") &&
        props.counter.status === "CALLED" &&
        props.counter.queue &&
        props.counter.queue.call_count < 3
    );
});

const canStart = computed(() => {
    return (
        can("queue.start") &&
        props.counter.status === "CALLED"
    );
});

const canFinish = computed(() => {
    return (
        can("queue.finish") &&
        props.counter.status === "SERVING"
    );
});

const canSkip = computed(() => {
    return (
        can("queue.skip") &&
        props.counter.status === "CALLED" &&
        props.counter.queue &&
        props.counter.queue.call_count >= 3
    );
});
</script>

<template>
    <section
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <!-- HEADER -->
        <div class="border-b border-slate-100 px-6 py-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400"
                    >
                        Loket Anda
                    </p>

                    <div class="mt-2 flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-slate-800">
                            {{ counter.name }}
                        </h2>

                        <span
                            class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600"
                        >
                            {{ counter.code }}
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ counter.service?.name ?? "Layanan" }}
                    </p>
                </div>

                <span
                    :class="statusConfig.badge"
                    class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold"
                >
                    <span
                        :class="statusConfig.dot"
                        class="h-2 w-2 rounded-full"
                    ></span>

                    {{ statusConfig.label }}
                </span>
            </div>
        </div>

        <!-- CURRENT QUEUE -->
        <div class="p-6">
            <div
                v-if="counter.queue"
                class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-6 text-center"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-500"
                >
                    Nomor Antrian Saat Ini
                </p>

                <div
                    class="mt-2 text-6xl font-black tracking-tight text-indigo-700"
                >
                    {{ counter.queue.queue_number }}
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                    <span
                        class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm"
                    >
                        {{ queueStatusLabel }}
                    </span>

                    <span
                        class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm"
                    >
                        Panggilan ke-
                        {{ counter.queue.call_count }}
                    </span>
                </div>
            </div>

            <!-- EMPTY -->
            <div
                v-else
                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm"
                >
                    <PhoneIcon class="h-7 w-7 text-slate-400" />
                </div>

                <h3 class="mt-4 font-semibold text-slate-700">
                    Belum ada antrian
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Panggil antrian berikutnya untuk memulai pelayanan.
                </p>
            </div>

            <!-- ACTIONS -->
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <!-- CALL -->
                <button
                    v-if="canCall"
                    type="button"
                    :disabled="processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 sm:col-span-2"
                    @click="emit('call')"
                >
                    <PhoneIcon class="h-5 w-5" />

                    {{ processing ? "Memproses..." : "Panggil Berikutnya" }}
                </button>

                <!-- RECALL -->
                <button
                    v-if="canRecall"
                    type="button"
                    :disabled="processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="emit('recall')"
                >
                    <ArrowPathIcon class="h-5 w-5" />

                    Panggil Ulang
                </button>

                <!-- START -->
                <button
                    v-if="canStart"
                    type="button"
                    :disabled="processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="emit('start')"
                >
                    <PlayIcon class="h-5 w-5" />

                    Mulai Pelayanan
                </button>

                <!-- FINISH -->
                <button
                    v-if="canFinish"
                    type="button"
                    :disabled="processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 sm:col-span-2"
                    @click="emit('finish')"
                >
                    <CheckCircleIcon class="h-5 w-5" />

                    Selesaikan
                </button>

                <!-- SKIP -->
                <button
                    v-if="canSkip"
                    type="button"
                    :disabled="processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="emit('skip')"
                >
                    <ForwardIcon class="h-5 w-5" />

                    Lewati Antrian
                </button>
            </div>

            <!-- MAX CALL WARNING -->
            <div
                v-if="
                    counter.status === 'CALLED' &&
                    counter.queue?.call_count >= 3
                "
                class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                <strong>Perhatian:</strong>
                nomor ini sudah dipanggil 3 kali. Silakan mulai pelayanan
                atau lewati antrian.
            </div>
        </div>
    </section>
</template>