<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { success } from "@/lib/swal";
import SearchBox from "@/Components/Shared/SearchBox.vue";

const props = defineProps({
    queues: Object,
    services: Array,
    filters: Object,
});

const page = usePage();

const search = ref(props.filters.search ?? "");

watch(search, (value) => {
    router.get(
        route("admin.queues.index"),
        {
            search: value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const form = useForm({
    service_id: "",
});

const submit = () => {
    form.post(route("admin.queues.store"), {
        preserveScroll: true,

        onSuccess: () => {
            success("Nomor antrian berhasil dibuat.");
            form.reset();
        },
    });
};

const badge = (status) => {
    switch (status) {
        case "WAITING":
            return "bg-yellow-100 text-yellow-700";

        case "CALLED":
            return "bg-blue-100 text-blue-700";

        case "SERVING":
            return "bg-green-100 text-green-700";

        case "FINISHED":
            return "bg-gray-200 text-gray-700";

        default:
            return "bg-red-100 text-red-700";
    }
};

</script>

<template>
    <Head title="Queue" />

    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Antrian</h1>
        </div>

        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-5">Ambil Nomor Antrian</h2>

            <form class="flex gap-4" @submit.prevent="submit" novalidate>
                <select
                    v-model="form.service_id"
                    class="border rounded-lg px-4 py-3 w-72"
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

                <button class="bg-indigo-600 text-white px-6 rounded-lg">
                    Ambil Nomor
                </button>
            </form>
            <p v-if="form.errors.service_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.service_id }}
            </p>
        </div>

        <SearchBox
            v-model="search"
            placeholder="Cari nomor..."
        />

        <table class="w-full bg-white rounded-xl overflow-hidden shadow">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-4">Nomor</th>

                    <th>Layanan</th>

                    <th>Loket</th>

                    <th>Status</th>

                    <th>Petugas</th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="queue in queues.data"
                    :key="queue.id"
                    class="border-b text-center"
                >
                    <td class="p-4 font-bold">
                        {{ queue.queue_number }}
                    </td>

                    <td>
                        {{ queue.service.name }}
                    </td>

                    <td>
                        {{ queue.counter?.code ?? "-" }}
                    </td>

                    <td>
                        <span
                            :class="badge(queue.status)"
                            class="px-3 py-1 rounded-full text-sm font-semibold"
                        >
                            {{ queue.status }}
                        </span>
                    </td>

                    <td>
                        {{ queue.handled_by?.name ?? "-" }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="flex gap-2 mt-6">
            <Link
                v-for="link in queues.links"
                :key="link.label"
                :href="link.url ?? ''"
                v-html="link.label"
                class="border px-4 py-2 rounded"
                :class="{
                    'bg-indigo-600 text-white': link.active,
                    'pointer-events-none opacity-50': !link.url,
                }"
            />
        </div>
    </AdminLayout>
</template>
