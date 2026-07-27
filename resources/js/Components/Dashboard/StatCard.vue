<script setup>
import { computed } from "vue";
import {
    ClipboardDocumentListIcon,
    ClockIcon,
    CheckCircleIcon,
    ChartBarIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },

    value: {
        type: [String, Number],
        required: true,
    },

    subtitle: {
        type: String,
        default: "",
    },

    color: {
        type: String,
        default: "blue",
    },
});

const theme = computed(() => {
    switch (props.color) {
        case "emerald":
            return {
                icon: CheckCircleIcon,
                bg: "bg-emerald-50",
                iconColor: "text-emerald-600",
            };

        case "amber":
            return {
                icon: ClockIcon,
                bg: "bg-amber-50",
                iconColor: "text-amber-600",
            };

        case "violet":
            return {
                icon: ChartBarIcon,
                bg: "bg-violet-50",
                iconColor: "text-violet-600",
            };

        case "blue":
        default:
            return {
                icon: ClipboardDocumentListIcon,
                bg: "bg-blue-50",
                iconColor: "text-blue-700",
            };
    }
});
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
    >
        <div class="flex items-start justify-between">
            <div>
                <p
                    class="text-sm font-medium uppercase tracking-wide text-slate-500"
                >
                    {{ title }}
                </p>

                <h2 class="mt-3 text-4xl font-bold tracking-tight text-slate-800">
                    {{ value }}
                </h2>

                <p
                    v-if="subtitle"
                    class="mt-2 text-sm text-slate-500"
                >
                    {{ subtitle }}
                </p>
            </div>

            <div
                :class="theme.bg"
                class="flex h-14 w-14 items-center justify-center rounded-2xl"
            >
                <component
                    :is="theme.icon"
                    :class="theme.iconColor"
                    class="h-7 w-7"
                />
            </div>
        </div>
    </div>
</template>