<script setup>
import {
    BuildingOffice2Icon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    counter: {
        type: String,
        default: "-",
    },

    queue: {
        type: String,
        default: null,
    },

    service: {
        type: String,
        default: "",
    },

    status: {
        type: String,
        default: "offline",
    },

    onlineStaffCount: {
        type: Number,
        default: 0,
    },

    staffCount: {
        type: Number,
        default: 0,
    },
});

/*
|--------------------------------------------------------------------------
| STATUS THEME
|--------------------------------------------------------------------------
*/

const statusTheme = () => {
    switch (props.status) {

        /*
        |--------------------------------------------------------------------------
        | AVAILABLE
        |--------------------------------------------------------------------------
        */

        case "available":
            return {
                badge: "bg-emerald-100 text-emerald-700",
                text: "TERSEDIA",
                icon: CheckCircleIcon,
            };

        /*
        |--------------------------------------------------------------------------
        | CALLED
        |--------------------------------------------------------------------------
        */

        case "called":
            return {
                badge: "bg-blue-100 text-blue-700",
                text: "DIPANGGIL",
                icon: ClockIcon,
            };

        /*
        |--------------------------------------------------------------------------
        | PROCESSING
        |--------------------------------------------------------------------------
        */

        case "processing":
            return {
                badge: "bg-amber-100 text-amber-700",
                text: "MELAYANI",
                icon: CheckCircleIcon,
            };

        /*
        |--------------------------------------------------------------------------
        | OFFLINE
        |--------------------------------------------------------------------------
        */

        default:
            return {
                badge: "bg-red-100 text-red-700",
                text: "TIDAK AKTIF",
                icon: XCircleIcon,
            };
    }
};

/*
|--------------------------------------------------------------------------
| QUEUE COLOR
|--------------------------------------------------------------------------
*/

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
    <div
        class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
    >
        <!-- =========================================================
             HEADER
        ========================================================== -->
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p
                    class="text-sm font-medium uppercase tracking-wide text-slate-500"
                >
                    {{ counter }}
                </p>

                <p class="mt-1 truncate text-xs text-slate-400">
                    {{ service || "Tidak Aktif" }}
                </p>
            </div>

            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100"
            >
                <BuildingOffice2Icon
                    class="h-6 w-6 text-slate-600"
                />
            </div>
        </div>

        <!-- =========================================================
             BODY
        ========================================================== -->
        <div class="flex flex-1 items-center justify-center">
            <h2
                :class="[
                    'text-5xl font-black tracking-tight',
                    queueColor(),
                ]"
            >
                {{
                    status === "offline"
                        ? "--"
                        : queue || "--"
                }}
            </h2>
        </div>

        <!-- =========================================================
             FOOTER
        ========================================================== -->
        <div class="flex items-center justify-between gap-3">

            <!-- STATUS -->
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

            <!-- ONLINE STAFF -->
            <span
                v-if="staffCount > 0"
                class="text-xs font-medium text-slate-400"
            >
                {{ onlineStaffCount }}/{{ staffCount }} online
            </span>
        </div>
    </div>
</template>