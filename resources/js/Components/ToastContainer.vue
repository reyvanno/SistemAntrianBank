<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import {
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

const toasts = ref([]);
const timers = new Map();

const show = (event) => {
    const toast = {
        id: event.detail.id,
        type: event.detail.type,
        message: event.detail.message,
    };

    toasts.value.push(toast);

    const timer = setTimeout(() => {
        remove(toast.id);
    }, 4000);

    timers.set(toast.id, timer);
};

const remove = (id) => {
    const timer = timers.get(id);

    if (timer) {
        clearTimeout(timer);
        timers.delete(id);
    }

    toasts.value = toasts.value.filter(
        (toast) => toast.id !== id
    );
};

const icon = (type) => {
    switch (type) {
        case "success":
            return CheckCircleIcon;

        case "error":
            return XCircleIcon;

        case "warning":
            return ExclamationTriangleIcon;

        default:
            return CheckCircleIcon;
    }
};

const containerClass = (type) => {
    switch (type) {
        case "success":
            return "border-emerald-200 bg-emerald-50 text-emerald-700";

        case "error":
            return "border-red-200 bg-red-50 text-red-700";

        case "warning":
            return "border-amber-200 bg-amber-50 text-amber-700";

        default:
            return "border-slate-200 bg-white text-slate-700";
    }
};

onMounted(() => {
    window.addEventListener("show-toast", show);
});

onUnmounted(() => {
    window.removeEventListener("show-toast", show);

    timers.forEach((timer) => clearTimeout(timer));
    timers.clear();
});
</script>

<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed right-6 top-6 z-[10000] flex w-full max-w-sm flex-col gap-3"
        >
            <TransitionGroup
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="translate-x-8 opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-8 opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto flex items-start gap-3 rounded-xl border bg-white p-4 shadow-lg"
                    :class="containerClass(toast.type)"
                >
                    <component
                        :is="icon(toast.type)"
                        class="mt-0.5 h-6 w-6 shrink-0"
                    />

                    <p
                        class="flex-1 text-sm font-medium leading-relaxed"
                    >
                        {{ toast.message }}
                    </p>

                    <button
                        type="button"
                        @click="remove(toast.id)"
                        class="shrink-0 opacity-60 transition hover:opacity-100"
                        aria-label="Tutup notifikasi"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>