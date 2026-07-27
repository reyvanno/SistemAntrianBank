<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    counter: {
        type: Object,
        default: () => ({
            service_id: "",
            code: "",
            name: "",
            is_active: true,
        }),
    },

    services: {
        type: Array,
        default: () => [],
    },

    submitRoute: String,
    submitMethod: String,
});

const form = useForm({
    service_id: props.counter.service_id,
    code: props.counter.code,
    name: props.counter.name,
    is_active: props.counter.is_active,
});

const submit = () => {
    if (props.submitMethod === "post") {
        form.post(route(props.submitRoute), { preserveScroll: true });
        return;
    }

    form.put(route(props.submitRoute, props.counter.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6" novalidate>
        <div>
            <label class="font-medium"> Layanan </label>

            <select
                v-model="form.service_id"
                class="border rounded-lg w-full p-3 mt-2"
            >
                <option value="">Pilih Layanan</option>

                <option
                    v-for="service in services"
                    :key="service.id"
                    :value="service.id"
                >
                    {{ service.name }}
                </option>
            </select>

            <p v-if="form.errors.service_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.service_id }}
            </p>
        </div>

        <div>
            <label class="font-medium"> Kode Loket </label>

            <input
                v-model="form.code"
                class="border rounded-lg w-full p-3 mt-2"
            />

            <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                {{ form.errors.code }}
            </p>
        </div>

        <div>
            <label class="font-medium"> Nama Loket </label>

            <input
                v-model="form.name"
                class="border rounded-lg w-full p-3 mt-2"
            />

            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                {{ form.errors.name }}
            </p>
        </div>

        <div>
            <label class="font-medium"> Status </label>

            <select
                v-model="form.is_active"
                class="border rounded-lg w-full p-3 mt-2"
            >
                <option :value="true">Aktif</option>

                <option :value="false">Tidak Aktif</option>
            </select>

            <p v-if="form.errors.is_active" class="mt-1 text-sm text-red-600">
                {{ form.errors.is_active }}
            </p>
        </div>

        <button class="bg-indigo-600 text-white px-6 py-3 rounded-lg">
            Simpan
        </button>
    </form>
</template>
