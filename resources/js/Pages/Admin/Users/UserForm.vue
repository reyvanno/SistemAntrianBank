<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { roleLabel } from "@/lib/role";
import { EyeIcon, EyeSlashIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    user: {
        type: Object,
        default: () => ({
            role: "",
            username: "",
            name: "",
            email: "",
            password: "",
            is_active: true,
        }),
    },

    roles: Array,

    submitRoute: String,
    submitMethod: String,
});

const form = useForm({
    role: props.user.roles?.length ? props.user.roles[0].name : "",
    username: props.user.username ?? "",
    name: props.user.name ?? "",
    email: props.user.email ?? "",
    password: "",
    is_active: props.user.is_active ?? true,
});

const showPassword = ref(false);

const submit = () => {
    if (props.submitMethod === "post") {
        form.post(route(props.submitRoute), {
            preserveScroll: true,
        });

        return;
    }

    form.put(route(props.submitRoute, props.user.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6" novalidate>
        <!-- Role -->
        <div>
            <label class="font-semibold text-slate-700"> Role </label>

            <select
                v-model="form.role"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">Pilih Role</option>

                <option v-for="role in roles" :key="role.id" :value="role.name">
                    {{ roleLabel(role.name) }}
                </option>
            </select>

            <p v-if="form.errors.role" class="mt-2 text-sm text-red-600">
                {{ form.errors.role }}
            </p>
        </div>

        <!-- Username -->
        <div>
            <label class="font-semibold text-slate-700"> Username </label>

            <input
                v-model="form.username"
                type="text"
                placeholder="Masukkan username"
                autocomplete="username"
                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100"
            />

            <p class="mt-2 text-xs text-slate-500">
                Gunakan huruf, angka, tanda hubung (-), atau underscore (_).
            </p>

            <p v-if="form.errors.username" class="mt-2 text-sm text-red-600">
                {{ form.errors.username }}
            </p>
        </div>

        <!-- Nama -->
        <div>
            <label class="font-semibold text-slate-700"> Nama </label>

            <input
                v-model="form.name"
                placeholder="Masukkan nama user"
                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100"
            />

            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                {{ form.errors.name }}
            </p>
        </div>

        <!-- Email -->
        <div>
            <label class="font-semibold text-slate-700"> Email </label>

            <input
                type="email"
                v-model="form.email"
                placeholder="contoh@email.com"
                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100"
            />

            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                {{ form.errors.email }}
            </p>
        </div>

        <!-- Password -->
        <div>
            <label class="font-semibold text-slate-700"> Password </label>

            <div class="relative mt-2">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    v-model="form.password"
                    placeholder="Masukkan Password"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-500 hover:text-indigo-600"
                    @click="showPassword = !showPassword"
                >
                    <EyeIcon v-if="!showPassword" class="h-5 w-5" />

                    <EyeSlashIcon v-else class="h-5 w-5" />
                </button>
            </div>

            <p class="mt-2 text-sm text-slate-500">
                {{
                    submitMethod === "put"
                        ? "Kosongkan jika password tidak ingin diubah."
                        : "Password minimal 8 karakter."
                }}
            </p>

            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                {{ form.errors.password }}
            </p>
        </div>

        <!-- Status -->
        <div>
            <label class="font-semibold text-slate-700"> Status </label>

            <select
                v-model="form.is_active"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100"
            >
                <option :value="true">Aktif</option>

                <option :value="false">Tidak Aktif</option>
            </select>

            <p v-if="form.errors.is_active" class="mt-2 text-sm text-red-600">
                {{ form.errors.is_active }}
            </p>
        </div>

        <PrimaryButton type="submit" class="w-full" :disabled="form.processing">
            {{ form.processing ? "Menyimpan..." : "Simpan" }}
        </PrimaryButton>
    </form>
</template>
