<script setup>
import { Head } from "@inertiajs/vue3";
import { onMounted, computed } from "vue";
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
});

onMounted(() => {
    document.documentElement.requestFullscreen?.();
});

const totalWaiting = computed(() =>
    Object.values(props.waitingQueues).reduce(
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
    if (!counter.queues.length) {
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
</script>

<template>
    <Head title="Monitor" />

    <div class="flex h-screen flex-col overflow-hidden bg-slate-100">
        <MonitorNavbar />

        <main
            class="grid flex-1 grid-rows-[auto_240px_minmax(0,1fr)] gap-4 overflow-hidden p-4"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold tracking-tight text-slate-800">
                    Sedang Dipanggil
                </h2>

                <span
                    class="rounded-full bg-slate-200 px-4 py-2 text-sm font-medium text-slate-600"
                >
                    {{ counters.length }} Loket
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
                        Total : {{ totalWaiting }} Antrian
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

                <span class="mx-16">
                    Terima kasih atas kunjungan Anda
                </span>

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

                <span class="mx-16">
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