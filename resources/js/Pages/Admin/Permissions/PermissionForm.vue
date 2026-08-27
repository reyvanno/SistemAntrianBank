<script setup>
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    permission: {
        type: Object,
        default: () => ({
            name: "",
        }),
    },

    submitRoute: {
        type: String,
        required: true,
    },

    submitMethod: {
        type: String,
        default: "post",
    },
});

const form = useForm({
    name: props.permission.name ?? "",
});

const submit = () => {
    if (props.submitMethod === "post") {
        form.post(route(props.submitRoute), {
            preserveScroll: true,
        });

        return;
    }

    form.put(
        route(
            props.submitRoute,
            props.permission.id
        ),
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <form
        @submit.prevent="submit"
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <h2 class="text-lg font-semibold text-slate-800">
            Informasi Permission
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Gunakan format
            <span class="font-semibold">modul.aksi</span>.
        </p>

        <!-- NAME -->
        <div class="mt-6">
            <InputLabel
                for="name"
                value="Nama Permission"
            />

            <input
                id="name"
                v-model="form.name"
                type="text"
                required
                autofocus
                placeholder="Contoh: queue.call"
                class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                permission yang sudah digunakan aplikasi tanpa
                memperbarui pengecekan permission tersebut.
            </p>
        </div>

        <!-- ACTION -->
        <div class="mt-6">
            <PrimaryButton
                type="submit"
                :disabled="form.processing"
                :class="{
                    'opacity-25': form.processing,
                }"
            >
                {{
                    form.processing
                        ? "Menyimpan..."
                        : submitMethod === "post"
                          ? "Simpan Permission"
                          : "Simpan Perubahan"
                }}
            </PrimaryButton>
        </div>
    </form>
</template>