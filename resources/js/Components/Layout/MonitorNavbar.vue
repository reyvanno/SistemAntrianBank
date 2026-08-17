<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { CalendarDaysIcon } from "@heroicons/vue/24/outline";

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
    <header class="h-16 border-b border-slate-200 bg-white shadow-sm">
        <div
            class="mx-auto flex h-full max-w-[1920px] items-center justify-between px-8"
        >
            <!-- Logo -->
            <div class="flex items-center gap-4">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100"
                >
                    🏦
                </div>

                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-800"
                    >
                        Sistem Antrian Bank
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Monitor Antrian Digital
                    </p>
                </div>
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
                    <span
                        class="h-2.5 w-2.5 rounded-full bg-emerald-500"
                    ></span>

                    <p class="text-lg font-bold text-slate-800">
                        {{ time }}
                    </p>
                </div>
            </div>
        </div>
    </header>
</template>
