<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        default: () => ({
            role_id: "",
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
    role_id: props.user.role_id,
    name: props.user.name,
    email: props.user.email,
    password: "",
    is_active: props.user.is_active ?? true,
});

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
        <div>
            <label class="font-medium"> Role </label>

            <select
                v-model="form.role_id"
                class="border rounded-lg w-full p-3 mt-2"
            >
                <option value="">Pilih Role</option>

                <option v-for="role in roles" :key="role.id" :value="role.id">
                    {{ role.description }}
                </option>
            </select>
            <p v-if="form.errors.role_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.role_id }}
            </p>
        </div>

        <div>
            <label class="font-medium"> Nama </label>

            <input
                v-model="form.name"
                class="border rounded-lg w-full p-3 mt-2"
            />

            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                {{ form.errors.name }}
            </p>
        </div>

        <div>
            <label class="font-medium"> Email </label>

            <input
                type="email"
                v-model="form.email"
                class="border rounded-lg w-full p-3 mt-2"
            />

            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                {{ form.errors.email }}
            </p>
        </div>

        <div>
            <label class="font-medium"> Password </label>

            <input
                type="password"
                v-model="form.password"
                class="border rounded-lg w-full p-3 mt-2"
            />

            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                {{ form.errors.password }}
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
