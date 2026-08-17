<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    role: {
        type: Object,
        required: true,
    },

    permissions: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
|
| Ambil permission yang saat ini dimiliki role.
|
*/

const form = useForm({
    name: props.role.name ?? "",
    description: props.role.description ?? "",

    permissions:
        props.role.permissions?.map(
            (permission) => permission.id
        ) ?? [],
});

/*
|--------------------------------------------------------------------------
| Group Permissions
|--------------------------------------------------------------------------
*/

const permissionGroups = computed(() => {
    const groups = {};

    props.permissions.forEach((permission) => {
        const [module] = permission.name.split(".");

        if (!groups[module]) {
            groups[module] = [];
        }

        groups[module].push(permission);
    });

    return groups;
});

/*
|--------------------------------------------------------------------------
| Module Label
|--------------------------------------------------------------------------
*/

const moduleLabel = (module) => {
    const labels = {
        dashboard: "Dashboard",
        user: "User",
        service: "Layanan",
        counter: "Loket",
        queue: "Antrian",
        monitor: "Monitor",
        report: "Laporan",
        role: "Role",
    };

    return (
        labels[module] ||
        module
            .replace(/_/g, " ")
            .replace(/\b\w/g, (letter) => letter.toUpperCase())
    );
};

/*
|--------------------------------------------------------------------------
| Permission Label
|--------------------------------------------------------------------------
*/

const permissionLabel = (permission) => {
    const action = permission.name.split(".")[1];

    const labels = {
        view: "Lihat",
        create: "Tambah",
        update: "Edit",
        delete: "Hapus",
        call: "Panggil",
        start: "Mulai",
        finish: "Selesaikan",
        cancel: "Batalkan",
        export: "Export",
    };

    return (
        labels[action] ||
        action
            .replace(/_/g, " ")
            .replace(/\b\w/g, (letter) => letter.toUpperCase())
    );
};

/*
|--------------------------------------------------------------------------
| Permission Check
|--------------------------------------------------------------------------
*/

const hasPermission = (permissionId) => {
    return form.permissions.includes(permissionId);
};

const togglePermission = (permissionId) => {
    if (hasPermission(permissionId)) {
        form.permissions = form.permissions.filter(
            (id) => id !== permissionId
        );

        return;
    }

    form.permissions.push(permissionId);
};

/*
|--------------------------------------------------------------------------
| Group Selection
|--------------------------------------------------------------------------
*/

const isGroupFullySelected = (permissions) => {
    return permissions.every((permission) =>
        hasPermission(permission.id)
    );
};

const isGroupPartiallySelected = (permissions) => {
    const selectedCount = permissions.filter((permission) =>
        hasPermission(permission.id)
    ).length;

    return (
        selectedCount > 0 &&
        selectedCount < permissions.length
    );
};

const toggleGroup = (permissions) => {
    const ids = permissions.map(
        (permission) => permission.id
    );

    if (isGroupFullySelected(permissions)) {
        form.permissions = form.permissions.filter(
            (id) => !ids.includes(id)
        );

        return;
    }

    const merged = new Set([
        ...form.permissions,
        ...ids,
    ]);

    form.permissions = [...merged];
};

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    form.put(
        route("admin.roles.update", props.role.id),
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <Head :title="`Edit Role - ${role.name}`" />

    <AdminLayout>
        <!-- HEADER -->
        <div class="mb-8">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.roles.index')"
                    class="text-sm font-medium text-slate-500 transition hover:text-indigo-600"
                >
                    Role Management
                </Link>

                <span class="text-slate-400">
                    /
                </span>

                <span class="text-sm font-medium text-slate-800">
                    Edit Role
                </span>
            </div>

            <div class="mt-4">
                <h1 class="text-3xl font-bold text-slate-800">
                    Edit Role
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Ubah informasi dan permission role
                    <span class="font-semibold text-slate-700">
                        {{ role.name }}
                    </span>
                    .
                </p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
                <!-- INFORMASI ROLE -->
                <div class="xl:col-span-1">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2
                            class="text-lg font-semibold text-slate-800"
                        >
                            Informasi Role
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Ubah identitas role.
                        </p>

                        <!-- NAME -->
                        <div class="mt-6">
                            <InputLabel
                                for="name"
                                value="Nama Role"
                            />

                            <TextInput
                                id="name"
                                type="text"
                                class="mt-2 block w-full"
                                v-model="form.name"
                                required
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mt-5">
                            <InputLabel
                                for="description"
                                value="Deskripsi"
                            />

                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="5"
                                placeholder="Deskripsi role..."
                                class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>

                            <InputError
                                class="mt-2"
                                :message="
                                    form.errors.description
                                "
                            />
                        </div>

                        <!-- USER COUNT -->
                        <div
                            class="mt-6 rounded-xl bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-slate-500"
                            >
                                Pengguna Role
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-slate-800"
                            >
                                {{ role.users_count ?? 0 }}
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                user menggunakan role ini.
                            </p>
                        </div>

                        <!-- PERMISSION COUNT -->
                        <div
                            class="mt-3 rounded-xl bg-indigo-50 p-4"
                        >
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-indigo-600"
                            >
                                Permission Dipilih
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-indigo-800"
                            >
                                {{ form.permissions.length }}
                            </p>

                            <p
                                class="mt-1 text-xs text-indigo-600"
                            >
                                permission aktif untuk role ini.
                            </p>
                        </div>

                        <!-- ACTION -->
                        <div
                            class="mt-6 flex flex-col gap-3"
                        >
                            <PrimaryButton
                                type="submit"
                                class="w-full justify-center"
                                :class="{
                                    'opacity-25':
                                        form.processing,
                                }"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? "Menyimpan..."
                                        : "Simpan Perubahan"
                                }}
                            </PrimaryButton>

                            <Link
                                :href="
                                    route(
                                        'admin.roles.index'
                                    )
                                "
                                class="inline-flex w-full items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Batal
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- PERMISSIONS -->
                <div class="xl:col-span-2">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h2
                                    class="text-lg font-semibold text-slate-800"
                                >
                                    Permissions
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Atur permission yang dimiliki
                                    role ini.
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700"
                            >
                                {{ form.permissions.length }}
                                dipilih
                            </span>
                        </div>

                        <div
                            v-if="permissions.length === 0"
                            class="mt-6 rounded-xl border border-dashed border-slate-300 p-8 text-center"
                        >
                            <p
                                class="font-medium text-slate-700"
                            >
                                Belum ada permission.
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Belum ada permission yang
                                tersedia.
                            </p>
                        </div>

                        <div
                            v-else
                            class="mt-6 space-y-5"
                        >
                            <div
                                v-for="(
                                    groupPermissions,
                                    module
                                ) in permissionGroups"
                                :key="module"
                                class="overflow-hidden rounded-xl border border-slate-200"
                            >
                                <!-- GROUP HEADER -->
                                <div
                                    class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4"
                                >
                                    <div>
                                        <h3
                                            class="font-semibold text-slate-800"
                                        >
                                            {{ moduleLabel(module) }}
                                        </h3>

                                        <p
                                            class="mt-0.5 text-xs text-slate-500"
                                        >
                                            {{
                                                groupPermissions.length
                                            }}
                                            permission
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        @click="
                                            toggleGroup(
                                                groupPermissions
                                            )
                                        "
                                        class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-800"
                                    >
                                        {{
                                            isGroupFullySelected(
                                                groupPermissions
                                            )
                                                ? "Batal Pilih"
                                                : "Pilih Semua"
                                        }}
                                    </button>
                                </div>

                                <!-- PERMISSION LIST -->
                                <div
                                    class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2"
                                >
                                    <label
                                        v-for="permission in groupPermissions"
                                        :key="permission.id"
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50"
                                        :class="{
                                            'border-indigo-300 bg-indigo-50':
                                                hasPermission(
                                                    permission.id
                                                ),
                                        }"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="
                                                hasPermission(
                                                    permission.id
                                                )
                                            "
                                            @change="
                                                togglePermission(
                                                    permission.id
                                                )
                                            "
                                            class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        />

                                        <div>
                                            <p
                                                class="text-sm font-semibold text-slate-800"
                                            >
                                                {{
                                                    permissionLabel(
                                                        permission
                                                    )
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs text-slate-500"
                                            >
                                                {{
                                                    permission.name
                                                }}
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <InputError
                            class="mt-4"
                            :message="
                                form.errors.permissions
                            "
                        />
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>