<script setup>
import {
    BuildingOffice2Icon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    counter: String,
    queue: String,
    service: String,
    status: {
        type: String,
        default: "offline",
    },
});

const statusTheme = () => {
    switch (props.status) {
        case "called":
            return {
                badge: "bg-blue-100 text-blue-700",
                text: "CALLED",
                icon: ClockIcon,
            };

        case "processing":
            return {
                badge: "bg-emerald-100 text-emerald-700",
                text: "PROCESSING",
                icon: CheckCircleIcon,
            };

        default:
            return {
                badge: "bg-red-100 text-red-700",
                text: "OFFLINE",
                icon: XCircleIcon,
            };
    }
};

const queueColor = () => {
    switch (props.service) {
        case "Teller":
            return "text-blue-700";

        case "Customer Service":
            return "text-emerald-600";

        case "Prioritas":
            return "text-amber-500";

        default:
            return "text-slate-300";
    }
};
</script>

<template>
    <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <p
                    class="text-sm font-medium uppercase tracking-wide text-slate-500"
                >
                    {{ counter }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    {{ service || "Tidak Aktif" }}
                </p>
            </div>

            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100"
            >
                <BuildingOffice2Icon class="h-6 w-6 text-slate-600" />
            </div>
        </div>

        <!-- Body -->
        <div class="flex flex-1 items-center justify-center">
            <h2
                :class="[
                    'text-5xl font-black tracking-tight',
                    queueColor(),
                ]"
            >
                {{ status === "offline" ? "--" : queue }}
            </h2>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between">
            <span
                :class="[
                    statusTheme().badge,
                    'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold',
                ]"
            >
                <component
                    :is="statusTheme().icon"
                    class="h-4 w-4"
                />

                {{ statusTheme().text }}
            </span>
        </div>
    </div>
</template>