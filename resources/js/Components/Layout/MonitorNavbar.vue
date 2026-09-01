<script setup>
import {
    ref,
    onMounted,
    onUnmounted,
} from "vue";

import {
    CalendarDaysIcon,
} from "@heroicons/vue/24/outline";

/*
|--------------------------------------------------------------------------
| CLOCK
|--------------------------------------------------------------------------
|
| Seluruh waktu sistem menggunakan:
|
| Asia/Jakarta
| WIB
|
| Tidak bergantung pada timezone komputer/client.
|
*/

const date = ref("");
const time = ref("");

const TIME_ZONE = "Asia/Jakarta";

/*
|--------------------------------------------------------------------------
| UPDATE CLOCK
|--------------------------------------------------------------------------
*/

const updateClock = () => {
    const now = new Date();

    /*
     * TANGGAL
     */

    date.value = new Intl.DateTimeFormat(
        "id-ID",
        {
            timeZone: TIME_ZONE,
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric",
        }
    ).format(now);

    /*
     * JAM
     */

    time.value = new Intl.DateTimeFormat(
        "id-ID",
        {
            timeZone: TIME_ZONE,
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: false,
        }
    ).format(now);
};

/*
|--------------------------------------------------------------------------
| TIMER
|--------------------------------------------------------------------------
*/

let timer = null;

onMounted(() => {
    updateClock();

    timer = window.setInterval(
        updateClock,
        1000
    );
});

onUnmounted(() => {
    if (timer) {
        window.clearInterval(timer);

        timer = null;
    }
});
</script>

<template>
    <header
        class="h-16 border-b border-slate-200 bg-white shadow-sm"
    >
        <div
            class="mx-auto flex h-full max-w-[1920px] items-center justify-between px-8"
        >

            <!-- =====================================================
                 LOGO
            ====================================================== -->

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

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Monitor Antrian Digital
                    </p>

                </div>

            </div>


            <!-- =====================================================
                 DATE & TIME
            ====================================================== -->

            <div class="flex items-center gap-4">

                <!-- DATE -->

                <div
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2"
                >

                    <CalendarDaysIcon
                        class="h-5 w-5 text-slate-500"
                    />

                    <span
                        class="text-sm font-medium text-slate-600"
                    >
                        {{ date }}
                    </span>

                </div>


                <!-- TIME -->

                <div
                    class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-2"
                >

                    <span
                        class="h-2.5 w-2.5 rounded-full bg-emerald-500"
                    ></span>

                    <p
                        class="text-lg font-bold tabular-nums text-slate-800"
                    >
                        {{ time }}
                    </p>

                </div>

            </div>

        </div>
    </header>
</template>