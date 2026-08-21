<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
});

const submit = () => {
    form.post(route("admin.permissions.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tambah Permission" />

    <AdminLayout>
        <!-- HEADER -->
        <div class="mb-8">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.permissions.index')"
                    class="text-sm font-medium text-slate-500 transition hover:text-indigo-600"
                >
                    Permission Management
                </Link>

                <span class="text-slate-400">
                    /
                </span>

                <span class="text-sm font-medium text-slate-800">
                    Tambah Permission
                </span>
            </div>

            <div class="mt-4">
                <h1 class="text-3xl font-bold text-slate-800">
                    Tambah Permission
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Tambahkan jenis akses baru yang dapat diberikan kepada role.
                </p>
            </div>
        </div>

        <!-- FORM -->
        <div class="max-w-2xl">
            <form
                @submit.prevent="submit"
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-slate-800">
                    Informasi Permission
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Gunakan format <span class="font-semibold">modul.aksi</span>.
                </p>

                <!-- NAME -->
                <div class="mt-6">
                    <InputLabel
                        for="name"
                        value="Nama Permission"
                    />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-2 block w-full"
                        v-model="form.name"
                        placeholder="Contoh: queue.call"
                        required
                        autofocus
                    />

                    <p class="mt-2 text-xs text-slate-500">
                        Contoh:
                        <span class="font-medium">
                            user.view
                        </span>,
                        <span class="font-medium">
                            queue.call
                        </span>,
                        <span class="font-medium">
                            report.export
                        </span>
                    </p>

                    <InputError
                        class="mt-2"
                        :message="form.errors.name"
                    />
                </div>

                <!-- WARNING -->
                <div
                    class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4"
                >
                    <p class="text-sm font-semibold text-amber-800">
                        Perhatikan nama permission
                    </p>

                    <p class="mt-1 text-sm text-amber-700">
                        Nama permission digunakan langsung oleh sistem
                        untuk pengecekan akses. Hindari mengganti nama
                        permission yang sudah digunakan oleh kode aplikasi
                        tanpa memperbarui pengecekan permission tersebut.
                    </p>
                </div>

                <!-- ACTION -->
                <div class="mt-6 flex gap-3">
                    <PrimaryButton
                        type="submit"
                        :class="{
                            'opacity-25': form.processing,
                        }"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? "Menyimpan..."
                                : "Simpan Permission"
                        }}
                    </PrimaryButton>

                    <Link
                        :href="route('admin.permissions.index')"
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>