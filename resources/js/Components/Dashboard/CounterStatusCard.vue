<script setup>
import {
    BuildingOffice2Icon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";
import { computed } from "vue";

const props = defineProps({
    counter: {
        type: Object,
        required: true,
    },
});

const config = computed(() => {
    switch (props.counter.status) {
        case "AVAILABLE":
            return {
                label: "Tersedia",
                badge: "bg-emerald-100 text-emerald-700",
                iconBg: "bg-emerald-50",
                iconColor: "text-emerald-600",
                icon: CheckCircleIcon,
            };

        case "CALLED":
            return {
                label: "Dipanggil",
                badge: "bg-blue-100 text-blue-700",
                iconBg: "bg-blue-50",
                iconColor: "text-blue-600",
                icon: ClockIcon,
            };

        case "SERVING":
            return {
                label: "Melayani",
                badge: "bg-amber-100 text-amber-700",
                iconBg: "bg-amber-50",
                iconColor: "text-amber-600",
                icon: ClockIcon,
            };

        case "INACTIVE":
            return {
                label: "Tidak Aktif",
                badge: "bg-red-100 text-red-700",
                iconBg: "bg-red-50",
                iconColor: "text-red-600",
                icon: XCircleIcon,
            };

        default:
            return {
                label: props.counter.status,
                badge: "bg-slate-100 text-slate-700",
                iconBg: "bg-slate-100",
                iconColor: "text-slate-500",
                icon: BuildingOffice2Icon,
            };
    }
});
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
        <!-- HEADER -->
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span
                        class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600"
                    >
                        {{ counter.code }}
                    </span>
                </div>

                <h3 class="mt-2 font-bold text-slate-800">
                    {{ counter.name }}
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    {{ counter.service?.name ?? "-" }}
                </p>
            </div>

            <div
                :class="config.iconBg"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
            >
                <component
                    :is="config.icon"
                    :class="config.iconColor"
                    class="h-5 w-5"
                />
            </div>
        </div>

        <!-- STATUS -->
        <div class="mt-5">
            <span
                :class="config.badge"
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
            >
                {{ config.label }}
            </span>
        </div>

        <!-- QUEUE -->
        <div
            v-if="counter.queue"
            class="mt-4 rounded-xl bg-slate-50 px-4 py-3"
        >
            <p class="text-xs text-slate-400">
                Antrian Saat Ini
            </p>

            <div class="mt-1 flex items-end justify-between gap-2">
                <p class="text-2xl font-black tracking-tight text-indigo-700">
                    {{ counter.queue.queue_number }}
                </p>

                <p class="text-xs text-slate-500">
                    Panggilan {{ counter.queue.call_count }}/3
                </p>
            </div>
        </div>

        <!-- NO QUEUE -->
        <div
            v-else
            class="mt-4 rounded-xl bg-slate-50 px-4 py-3"
        >
            <p class="text-sm text-slate-500">
                Tidak ada antrian aktif
            </p>
        </div>
    </div>
</template>