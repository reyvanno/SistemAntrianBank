<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";

import { Head, useForm, usePage } from "@inertiajs/vue3";

import { computed, ref } from "vue";

import {
    BuildingOffice2Icon,
    CheckCircleIcon,
    ClockIcon,
    TicketIcon,
    ArrowPathIcon,
    ArrowDownTrayIcon,
} from "@heroicons/vue/24/outline";

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

const props = defineProps({
    services: {
        type: Array,
        required: true,
    },
});

/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

const page = usePage();

/*
|--------------------------------------------------------------------------
| QUEUE RESULT
|--------------------------------------------------------------------------
|
| Queue berasal dari flash session Laravel.
|
*/

const queue = computed(() => {
    return page.props.flash?.queue ?? null;
});

/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

const form = useForm({
    service_id: "",
});

/*
|--------------------------------------------------------------------------
| SELECTED SERVICE
|--------------------------------------------------------------------------
*/

const selectedService = computed(() => {
    return props.services.find(
        (service) => String(service.id) === String(form.service_id),
    );
});

/*
|--------------------------------------------------------------------------
| SUBMIT
|--------------------------------------------------------------------------
*/

const submit = () => {
    if (!form.service_id || form.processing) {
        return;
    }

    form.post(route("customer.queue.store"), {
        preserveScroll: true,

        /*
         * Jangan reset form terlalu cepat
         * sebelum response Inertia selesai.
         */
        onSuccess: () => {
            /*
             * Queue hasil pembuatan akan
             * otomatis masuk ke:
             *
             * page.props.flash.queue
             *
             * sehingga computed `queue`
             * akan berubah secara reactive.
             */

            form.reset();
        },
    });
};

const downloadPdf = () => {
    if (!queue.value?.id) {
        return;
    }

    window.location.href = route("customer.queue.pdf", queue.value.id);
};
</script>

<template>
    <Head title="Ambil Nomor Antrian" />

    <GuestLayout>
        <div class="w-full max-w-4xl">
            <!-- =====================================================
                 HEADER
            ====================================================== -->

            <div class="mb-8 text-center">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50"
                >
                    <TicketIcon class="h-9 w-9 text-indigo-600" />
                </div>

                <h1 class="text-3xl font-bold text-slate-800">
                    Ambil Nomor Antrian
                </h1>

                <p
                    class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500"
                >
                    Pilih layanan yang ingin Anda gunakan, kemudian ambil nomor
                    antrian secara online.
                </p>
            </div>

            <!-- RESULT -->
            <div
                v-if="queue"
                class="mb-8 overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm"
            >
                <!-- RESULT HEADER -->

                <div
                    class="border-b border-emerald-100 bg-emerald-50 px-6 py-5"
                >
                    <div class="flex items-center gap-3">
                        <CheckCircleIcon
                            class="h-7 w-7 shrink-0 text-emerald-600"
                        />

                        <div>
                            <h2 class="font-bold text-emerald-800">
                                Nomor Antrian Berhasil Dibuat
                            </h2>

                            <p class="mt-0.5 text-sm text-emerald-600">
                                Silakan tunggu sampai nomor Anda dipanggil.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- TICKET -->

                <div class="p-8 text-center">
                    <p
                        class="text-sm font-medium uppercase tracking-wider text-slate-400"
                    >
                        Nomor Antrian Anda
                    </p>

                    <div
                        class="mt-3 text-7xl font-black tracking-tight text-indigo-600"
                    >
                        {{ queue.queue_number }}
                    </div>

                    <!-- SERVICE -->

                    <div
                        class="mx-auto mt-6 max-w-md rounded-xl bg-slate-50 p-4"
                    >
                        <div
                            class="flex items-center justify-center gap-2 text-slate-600"
                        >
                            <BuildingOffice2Icon class="h-5 w-5" />

                            <span class="font-semibold">
                                {{ queue.service }}
                            </span>
                        </div>

                        <!-- TIME -->

                        <div
                            class="mt-3 flex items-center justify-center gap-2 text-sm text-slate-500"
                        >
                            <ClockIcon class="h-4 w-4" />

                            <span>
                                Diambil pukul
                                {{ queue.created_at }}
                            </span>
                        </div>
                    </div>

                    <!-- STATUS -->

                    <div class="mt-6">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-yellow-50 px-4 py-2 text-sm font-semibold text-yellow-700"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-yellow-500"
                            ></span>

                            Menunggu
                        </span>
                    </div>

                    <!-- DOWNLOAD PDF -->

                    <div class="mt-6 border-t border-slate-100 pt-6">
                        <button
                            type="button"
                            @click="downloadPdf"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                        >
                            <ArrowDownTrayIcon class="h-5 w-5" />

                            <span> Unduh Tiket PDF </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 FORM
            ====================================================== -->

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- FORM HEADER -->

                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-bold text-slate-800">
                        Pilih Layanan
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Pilih layanan yang ingin Anda gunakan.
                    </p>
                </div>

                <form class="p-6" @submit.prevent="submit">
                    <!-- =================================================
                         SERVICES
                    ================================================== -->

                    <div
                        v-if="services.length"
                        class="grid gap-4 sm:grid-cols-2"
                    >
                        <button
                            v-for="service in services"
                            :key="service.id"
                            type="button"
                            class="group rounded-xl border p-5 text-left transition"
                            :class="
                                String(form.service_id) === String(service.id)
                                    ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-100'
                                    : 'border-slate-200 bg-white hover:border-indigo-300 hover:bg-slate-50'
                            "
                            @click="form.service_id = service.id"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <!-- ICON -->

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                                    :class="
                                        String(form.service_id) ===
                                        String(service.id)
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-50 group-hover:text-indigo-600'
                                    "
                                >
                                    <BuildingOffice2Icon class="h-6 w-6" />
                                </div>

                                <!-- CHECK -->

                                <div
                                    v-if="
                                        String(form.service_id) ===
                                        String(service.id)
                                    "
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600"
                                >
                                    <CheckCircleIcon
                                        class="h-5 w-5 text-white"
                                    />
                                </div>
                            </div>

                            <!-- SERVICE INFO -->

                            <div class="mt-4">
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    {{ service.code }}
                                </p>

                                <h3 class="mt-1 font-bold text-slate-800">
                                    {{ service.name }}
                                </h3>
                            </div>
                        </button>
                    </div>

                    <!-- =================================================
                         NO SERVICE
                    ================================================== -->

                    <div
                        v-else
                        class="rounded-xl border border-slate-200 bg-slate-50 px-5 py-8 text-center"
                    >
                        <p class="text-sm font-medium text-slate-600">
                            Belum ada layanan yang tersedia.
                        </p>
                    </div>

                    <!-- =================================================
                         ERROR
                    ================================================== -->

                    <p
                        v-if="form.errors.service_id"
                        class="mt-4 text-sm font-medium text-red-600"
                    >
                        {{ form.errors.service_id }}
                    </p>

                    <!-- =================================================
                         SUBMIT
                    ================================================== -->

                    <div class="mt-8 border-t border-slate-100 pt-6">
                        <button
                            type="submit"
                            :disabled="
                                !form.service_id ||
                                form.processing ||
                                !services.length
                            "
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <ArrowPathIcon
                                v-if="form.processing"
                                class="h-5 w-5 animate-spin"
                            />

                            <TicketIcon v-else class="h-5 w-5" />

                            <span>
                                {{
                                    form.processing
                                        ? "Membuat Nomor..."
                                        : "Ambil Nomor Antrian"
                                }}
                            </span>
                        </button>

                        <!-- SELECTED SERVICE -->

                        <p
                            v-if="selectedService"
                            class="mt-3 text-center text-xs text-slate-400"
                        >
                            Layanan dipilih:

                            <span class="font-semibold text-slate-600">
                                {{ selectedService.name }}
                            </span>
                        </p>
                    </div>
                </form>
            </div>

            <!-- =====================================================
                 INFO
            ====================================================== -->

            <div
                class="mt-6 rounded-xl border border-indigo-100 bg-indigo-50 px-5 py-4"
            >
                <p class="text-center text-sm leading-6 text-indigo-700">
                    Setelah mendapatkan nomor, silahkan menunggu hingga nomor
                    Anda dipanggil oleh petugas.
                </p>
            </div>
        </div>
    </GuestLayout>
</template>
