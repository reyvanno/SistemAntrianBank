<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Head, useForm } from "@inertiajs/vue3";
import {
    UserCircleIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowRightOnRectangleIcon,
} from "@heroicons/vue/24/outline";
import { ref } from "vue";

defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },

    status: {
        type: String,
        default: null,
    },
});

const showPassword = ref(false);

const form = useForm({
    login: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => {
            form.reset("password");
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Login" />

        <div class="w-full max-w-md">
            <!-- LOGIN CARD -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- CARD HEADER -->
                <div
                    class="border-b border-slate-200 px-6 py-6 sm:px-8"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50"
                        >
                            <UserCircleIcon
                                class="h-6 w-6 text-indigo-600"
                            />
                        </div>

                        <div>
                            <h2
                                class="text-xl font-bold text-slate-800"
                            >
                                Masuk ke Sistem
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500"
                            >
                                Sistem Antrian Bank
                            </p>
                        </div>
                    </div>
                </div>

                <!-- FORM -->
                <div class="px-6 py-7 sm:px-8">
                    <div
                        v-if="status"
                        class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                    >
                        {{ status }}
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="space-y-5"
                    >
                        <!-- USERNAME / EMAIL -->
                        <div>
                            <label
                                for="login"
                                class="block text-sm font-semibold text-slate-700"
                            >
                                Username atau Email
                            </label>

                            <div
                                class="relative mt-2"
                            >
                                <UserCircleIcon
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    id="login"
                                    v-model="form.login"
                                    type="text"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Masukkan username atau email"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                    :class="{
                                        'border-red-400 focus:border-red-500 focus:ring-red-100':
                                            form.errors.login,
                                    }"
                                />
                            </div>

                            <InputError
                                class="mt-2"
                                :message="form.errors.login"
                            />
                        </div>

                        <!-- PASSWORD -->
                        <div>
                            <label
                                for="password"
                                class="block text-sm font-semibold text-slate-700"
                            >
                                Password
                            </label>

                            <div
                                class="relative mt-2"
                            >
                                <LockClosedIcon
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="
                                        showPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    required
                                    autocomplete="current-password"
                                    placeholder="Masukkan password"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-12 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                    :class="{
                                        'border-red-400 focus:border-red-500 focus:ring-red-100':
                                            form.errors.password,
                                    }"
                                />

                                <button
                                    type="button"
                                    @click="
                                        showPassword =
                                            !showPassword
                                    "
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-indigo-600"
                                    :aria-label="
                                        showPassword
                                            ? 'Sembunyikan password'
                                            : 'Tampilkan password'
                                    "
                                >
                                    <EyeSlashIcon
                                        v-if="
                                            showPassword
                                        "
                                        class="h-5 w-5"
                                    />

                                    <EyeIcon
                                        v-else
                                        class="h-5 w-5"
                                    />
                                </button>
                            </div>

                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- OPTIONS -->
                        <div
                            class="flex items-center justify-between"
                        >
                            <label
                                class="flex cursor-pointer items-center gap-2"
                            >
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />

                                <span
                                    class="text-sm text-slate-600"
                                >
                                    Ingat saya
                                </span>
                            </label>

                            <span
                                v-if="canResetPassword"
                                class="text-xs text-slate-400"
                            >
                                Password terlupa?
                            </span>
                        </div>

                        <!-- BUTTON -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <ArrowRightOnRectangleIcon
                                v-if="!form.processing"
                                class="h-5 w-5"
                            />

                            <span>
                                {{
                                    form.processing
                                        ? "Memproses..."
                                        : "Masuk"
                                }}
                            </span>
                        </button>
                    </form>
                </div>

                <!-- CARD FOOTER -->
                <div
                    class="border-t border-slate-100 bg-slate-50 px-6 py-4 text-center sm:px-8"
                >
                    <p
                        class="text-xs leading-5 text-slate-500"
                    >
                        Gunakan username atau email yang
                        terdaftar pada sistem.
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>