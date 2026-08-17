<script setup>
import { computed, ref, onMounted, onUnmounted } from "vue";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },

    color: {
        type: String,
        default: "blue",
    },

    queues: {
        type: Array,
        default: () => [],
    },
});

const scrollContainer = ref(null);

let animation = null;

const theme = computed(() => {
    switch (props.color) {
        case "emerald":
            return {
                header: "bg-emerald-600",
                number: "text-emerald-600",
            };

        case "amber":
            return {
                header: "bg-amber-500",
                number: "text-amber-500",
            };

        default:
            return {
                header: "bg-blue-700",
                number: "text-blue-700",
            };
    }
});

const startAutoScroll = () => {
    const el = scrollContainer.value;

    if (!el || props.queues.length <= 3) {
        return;
    }

    animation = setInterval(() => {
        el.scrollTop += 1;

        /*
         * Setelah mencapai bagian bawah,
         * kembali ke awal secara halus.
         */
        if (el.scrollTop >= el.scrollHeight - el.clientHeight) {
            el.scrollTop = 0;
        }
    }, 30);
};

onMounted(() => {
    startAutoScroll();
});

onUnmounted(() => {
    clearInterval(animation);
});
</script>

<template>
    <div
        class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <!-- HEADER -->
        <div
            :class="[
                theme.header,
                'shrink-0 px-5 py-3 text-center text-lg font-semibold text-white',
            ]"
        >
            {{ props.title }}
        </div>

        <!-- SCROLL AREA -->
        <div
            ref="scrollContainer"
            class="min-h-0 flex-1 overflow-y-auto scrollbar-hide"
        >
            <div class="space-y-3 p-3">
                <!-- DATA ASLI -->
                <div
                    v-for="queue in props.queues"
                    :key="queue"
                    class="flex h-16 shrink-0 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-5"
                >
                    <span
                        :class="[
                            theme.number,
                            'text-3xl font-bold tracking-wide',
                        ]"
                    >
                        {{ queue }}
                    </span>

                    <span
                        class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600"
                    >
                        MENUNGGU
                    </span>
                </div>

                <!-- DUPLICATE UNTUK LOOP SCROLL -->
                <template v-if="props.queues.length > 3">
                    <div
                        v-for="queue in props.queues"
                        :key="`duplicate-${queue}`"
                        class="flex h-16 shrink-0 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-5"
                    >
                        <span
                            :class="[
                                theme.number,
                                'text-3xl font-bold tracking-wide',
                            ]"
                        >
                            {{ queue }}
                        </span>

                        <span
                            class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600"
                        >
                            MENUNGGU
                        </span>
                    </div>
                </template>

                <!-- EMPTY -->
                <div
                    v-if="props.queues.length === 0"
                    class="py-10 text-center text-sm text-slate-400"
                >
                    Tidak ada antrian
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>