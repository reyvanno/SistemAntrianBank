<script setup>
import { computed, ref, onMounted, onUnmounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import { CalendarDaysIcon } from "@heroicons/vue/24/outline";

const page = usePage();

const user = computed(() => page.props.auth.user);

const roleTitle = computed(() => {
    switch (user.value.role) {
        case "admin":
            return "Administrator";
        case "teller":
            return "Teller";
        case "customer_service":
            return "Customer Service";
        default:
            return "";
    }
});

const date = ref("");
const time = ref("");

const updateClock = () => {
    const now = new Date();

    date.value = new Intl.DateTimeFormat("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(now);

    time.value = now.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

let timer;

onMounted(() => {
    updateClock();
    timer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<template>
    <header
        class="flex h-20 items-center justify-between border-b border-slate-200 bg-white px-8"
    >
        <!-- Left -->
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-800">
                Dashboard
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ roleTitle }} Panel
            </p>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-4">
            <div
                class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2"
            >
                <CalendarDaysIcon class="h-5 w-5 text-slate-500" />

                <span class="text-sm font-medium text-slate-600">
                    {{ date }}
                </span>
            </div>

            <div
                class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-2"
            >
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>

                <p class="text-lg font-bold text-slate-800">
                        {{ time }}
                    </p>
            </div>
        </div>
    </header>
</template>