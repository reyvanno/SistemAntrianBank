<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { confirmDelete } from "@/lib/swal";
import SearchBox from "@/Components/Shared/SearchBox.vue";

const props = defineProps({
    users: Object,
    filters: Object,
});

const page = usePage();

const search = ref(props.filters.search ?? "");

watch(search, (value) => {
    router.get(
        route("admin.users.index"),
        {
            search: value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const destroy = async (id) => {

    const result = await confirmDelete();

    if (!result.isConfirmed) return;

    router.delete(
        route("admin.users.destroy", id)
    );

};
</script>

<template>
    <Head title="Master User" />

    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">
                Kelola User
            </h1>

            <Link
                :href="route('admin.users.create')"
                class="bg-indigo-600 text-white px-5 py-3 rounded-lg"
            >
                Tambah
            </Link>
        </div>

        <SearchBox
            v-model="search"
            placeholder="Cari user..."
        />
        
        <table class="w-full bg-white rounded-xl overflow-hidden shadow">
            <thead class="bg-slate-100">
                <tr class="border-b">
                    <th class="p-4">Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th width="180">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="user in users.data"
                    :key="user.id"
                    class="border-b text-center"
                >
                    <td class="p-4">
                        {{ user.name }}
                    </td>

                    <td>
                        {{ user.email }}
                    </td>

                    <td>
                        {{ user.role.description }}
                    </td>

                    <td>
                        <span
                            v-if="user.is_active"
                            class="text-green-600 font-semibold"
                        >
                            Aktif
                        </span>

                        <span
                            v-else
                            class="text-red-600 font-semibold"
                        >
                            Tidak Aktif
                        </span>
                    </td>

                    <td>
                        <div class="flex justify-center gap-2">
                            <Link
                                :href="route('admin.users.edit', user.id)"
                                class="bg-yellow-500 text-white px-4 py-2 rounded"
                            >
                                Edit
                            </Link>

                            <button
                                @click="destroy(user.id)"
                                class="bg-red-600 text-white px-4 py-2 rounded"
                            >
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>

                <tr v-if="users.data.length === 0">
                    <td colspan="5" class="py-8 text-gray-500">
                        Tidak ada data user.
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="flex gap-2 mt-6">
            <Link
                v-for="link in users.links"
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