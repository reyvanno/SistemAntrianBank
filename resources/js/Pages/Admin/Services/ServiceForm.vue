<script setup>
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    service: {
        type: Object,
        default: () => ({
            code: "",
            name: "",
        }),
    },

    submitRoute: String,
    submitMethod: String,
});

const form = useForm({
    code: props.service.code,
    name: props.service.name,
});

const submit = () => {
    if (props.submitMethod === "post") {
        form.post(route(props.submitRoute), {
            preserveScroll: true,
        });

        return;
    }

    form.put(route(props.submitRoute, props.service.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6" novalidate>
        <div>
            <label class="font-medium"> Kode </label>

            <input
                v-model="form.code"
                class="border rounded-lg w-full p-3 mt-2"
            />

            <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                {{ form.errors.code }}
            </p>
        </div>

        <div>
            <label class="font-medium"> Nama Layanan </label>

            <input
                v-model="form.name"
                class="border rounded-lg w-full p-3 mt-2"
            />

            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                {{ form.errors.name }}
            </p>
        </div>

        <PrimaryButton type="submit" class="w-full">
            Simpan
        </PrimaryButton>
    </form>
</template>
