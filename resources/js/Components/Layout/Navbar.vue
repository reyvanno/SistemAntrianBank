<script setup>
import {
    computed,
    ref,
    onMounted,
    onUnmounted,
} from "vue";

import {
    usePage,
} from "@inertiajs/vue3";

import {
    CalendarDaysIcon,
    BuildingLibraryIcon,
} from "@heroicons/vue/24/outline";

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

const props = defineProps({
    guest: {
        type: Boolean,
        default: false,
    },
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

const page = usePage();

const user = computed(() => {
    return page.props.auth?.user ?? null;
});

/*
|--------------------------------------------------------------------------
| ROLE
|--------------------------------------------------------------------------
*/

const roleTitle = computed(() => {

    switch (user.value?.role) {

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

/*
|--------------------------------------------------------------------------
| CLOCK
|--------------------------------------------------------------------------
*/

const date = ref("");
const time = ref("");

/*
 * Sistem menggunakan timezone Indonesia.
 */
const TIME_ZONE = "Asia/Jakarta";

/*
|--------------------------------------------------------------------------
| UPDATE CLOCK
|--------------------------------------------------------------------------
*/

const updateClock = () => {

    const now = new Date();

    /*
     * --------------------------------------------------------------
     * DATE
     * --------------------------------------------------------------
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
     * --------------------------------------------------------------
     * TIME
     * --------------------------------------------------------------
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

    /*
     * Update pertama langsung.
     */
    updateClock();

    /*
     * Update setiap detik.
     */
    timer = window.setInterval(
        updateClock,
        1000
    );

});

onUnmounted(() => {

    if (timer) {

        window.clearInterval(
            timer
        );

        timer = null;
    }

});
</script>

<template>

    <header
        class="flex h-20 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-8"
    >

        <!-- =========================================================
             ADMIN MODE
        ========================================================== -->

        <div v-if="!props.guest">

            <h1
                class="text-2xl font-semibold tracking-tight text-slate-800"
            >
                Dashboard
            </h1>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                {{ roleTitle }} Panel
            </p>

        </div>


        <!-- =========================================================
             GUEST MODE
        ========================================================== -->

        <div
            v-else
            class="flex items-center gap-3"
        >

            <!-- LOGO -->

            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100"
            >

                <BuildingLibraryIcon
                    class="h-7 w-7 text-indigo-600"
                />

            </div>


            <!-- BRAND -->

            <div>

                <h1
                    class="text-lg font-bold leading-tight text-slate-800"
                >
                    Sistem Antrian
                </h1>

                <p
                    class="text-sm text-slate-500"
                >
                    Sistem Antrian Digital
                </p>

            </div>

        </div>


        <!-- =========================================================
             DATE & TIME
        ========================================================== -->

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

    </header>

</template>