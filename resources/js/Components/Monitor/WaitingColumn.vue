<script setup>
import {
    computed,
    ref,
    onMounted,
    onUnmounted,
    nextTick,
    watch,
} from "vue";

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
const track = ref(null);

const offset = ref(0);

let animationFrame = null;
let lastTimestamp = null;
let loopHeight = 0;

const SCROLL_SPEED = 0.02;

/*
|--------------------------------------------------------------------------
| THEME
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| STOP
|--------------------------------------------------------------------------
*/

const stopAutoScroll = () => {
    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
        animationFrame = null;
    }

    lastTimestamp = null;
};

/*
|--------------------------------------------------------------------------
| CALCULATE LOOP HEIGHT
|--------------------------------------------------------------------------
*/

const calculateLoopHeight = async () => {
    await nextTick();

    const el = track.value;

    if (!el || props.queues.length === 0) {
        loopHeight = 0;
        offset.value = 0;

        return;
    }

    /*
     * Karena track berisi:
     *
     * SET 1
     * SET 2
     *
     * maka tinggi satu loop =
     * tinggi track / 2
     */
    loopHeight = el.scrollHeight / 2;

    offset.value = 0;
};

/*
|--------------------------------------------------------------------------
| ANIMATION
|--------------------------------------------------------------------------
*/

const startAutoScroll = async () => {
    stopAutoScroll();

    await calculateLoopHeight();

    if (
        props.queues.length <= 1 ||
        loopHeight <= 0
    ) {
        return;
    }

    const animate = (timestamp) => {
        if (!lastTimestamp) {
            lastTimestamp = timestamp;
        }

        const delta = Math.min(
            timestamp - lastTimestamp,
            32,
        );

        lastTimestamp = timestamp;

        /*
         * Gerakan konstan.
         */
        offset.value +=
            SCROLL_SPEED * delta;

        /*
         * Jangan tunggu sampai terlihat
         * mencapai ujung.
         *
         * Begitu sudah melewati tinggi
         * satu set, kita langsung kurangi.
         *
         * Karena di bawahnya sudah ada
         * salinan yang identik,
         * perubahan ini tidak terlihat.
         */
        if (offset.value >= loopHeight) {
            offset.value -= loopHeight;
        }

        animationFrame =
            requestAnimationFrame(animate);
    };

    animationFrame =
        requestAnimationFrame(animate);
};

/*
|--------------------------------------------------------------------------
| QUEUE CHANGE
|--------------------------------------------------------------------------
*/

const queueSignature = computed(() =>
    props.queues.join("|"),
);

let previousSignature = "";

watch(
    queueSignature,
    async (newSignature) => {
        if (
            newSignature ===
            previousSignature
        ) {
            return;
        }

        previousSignature = newSignature;

        await startAutoScroll();
    },
);

/*
|--------------------------------------------------------------------------
| LIFECYCLE
|--------------------------------------------------------------------------
*/

onMounted(async () => {
    previousSignature =
        queueSignature.value;

    await startAutoScroll();
});

onUnmounted(() => {
    stopAutoScroll();
});
</script>

<template>
    <div
        class="flex h-full min-h-0 min-w-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
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
            class="waiting-scroll min-h-0 flex-1 overflow-hidden overflow-x-hidden scrollbar-hide"
        >
            <div
                ref="track"
                :style="{
                    transform: `translate3d(0, -${offset}px, 0)`,
                    willChange: 'transform',
                }"
            >
                <!-- =================================================
                     SET PERTAMA
                ================================================== -->

                <div class="space-y-3 p-3">
                    <div
                        v-for="(queue, index) in props.queues"
                        :key="`first-${queue}-${index}`"
                        data-queue-item
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
                </div>

                <!-- =================================================
                     SET KEDUA
                ================================================== -->

                <div
                    v-if="props.queues.length > 0"
                    class="space-y-3 p-3"
                >
                    <div
                        v-for="(queue, index) in props.queues"
                        :key="`second-${queue}-${index}`"
                        data-queue-item
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
                </div>

                <!-- EMPTY -->

                <div
                    v-if="props.queues.length === 0"
                    class="flex min-h-[200px] items-center justify-center text-center text-sm text-slate-400"
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