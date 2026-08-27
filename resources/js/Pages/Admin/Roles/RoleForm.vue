<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    role: {
        type: Object,
        default: () => ({
            name: "",
            description: "",
            permissions: [],
        }),
    },

    permissions: {
        type: Array,
        default: () => [],
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
    name: props.role.name ?? "",
    description: props.role.description ?? "",
    permissions:
        props.role.permissions?.map(
            (permission) => permission.id
        ) ?? [],
});

/*
|--------------------------------------------------------------------------
| Permission Groups
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
| Labels
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
        permission: "Permission",
    };

    return (
        labels[module] ||
        module
            .replace(/_/g, " ")
            .replace(/\b\w/g, (letter) => letter.toUpperCase())
    );
};

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
| Permission Selection
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

const isGroupFullySelected = (permissions) => {
    return permissions.every((permission) =>
        hasPermission(permission.id)
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
    if (props.submitMethod === "post") {
        form.post(route(props.submitRoute), {
            preserveScroll: true,
        });

        return;
    }

    form.put(
        route(
            props.submitRoute,
            props.role.id
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
        class="grid grid-cols-1 gap-8 xl:grid-cols-3"
    >
        <!-- INFORMASI ROLE -->
        <div class="xl:col-span-1">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-slate-800">
                    Informasi Role
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Tentukan identitas role yang akan dibuat.
                </p>

                <!-- NAME -->
                <div class="mt-6">
                    <InputLabel
                        for="name"
                        value="Nama Role"
                    />

                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        placeholder="Contoh: Supervisor"
                        class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                        :message="form.errors.description"
                    />
                </div>

                <!-- SUMMARY -->
                <div
                    class="mt-6 rounded-xl bg-indigo-50 p-4"
                >
                    <p
                        class="text-sm font-medium text-indigo-700"
                    >
                        Permission dipilih
                    </p>

                    <p
                        class="mt-1 text-2xl font-bold text-indigo-800"
                    >
                        {{ form.permissions.length }}
                    </p>

                    <p
                        class="mt-1 text-xs text-indigo-600"
                    >
                        permission diberikan kepada role ini.
                    </p>
                </div>

                <!-- ACTION -->
                <div class="mt-6 flex flex-col gap-3">
                    <PrimaryButton
                        type="submit"
                        class="w-full justify-center"
                        :disabled="form.processing"
                        :class="{
                            'opacity-25': form.processing,
                        }"
                    >
                        {{
                            form.processing
                                ? "Menyimpan..."
                                : submitMethod === "post"
                                  ? "Simpan Role"
                                  : "Simpan Perubahan"
                        }}
                    </PrimaryButton>
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
                            Tentukan permission yang dimiliki role ini.
                        </p>
                    </div>

                    <span
                        class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700"
                    >
                        {{ form.permissions.length }}
                        dipilih
                    </span>
                </div>

                <!-- EMPTY -->
                <div
                    v-if="permissions.length === 0"
                    class="mt-6 rounded-xl border border-dashed border-slate-300 p-8 text-center"
                >
                    <p class="font-medium text-slate-700">
                        Belum ada permission.
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Tambahkan permission terlebih dahulu.
                    </p>
                </div>

                <!-- GROUPS -->
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
                        <!-- HEADER -->
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
                                    {{ groupPermissions.length }}
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

                        <!-- LIST -->
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
                                        {{ permission.name }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <InputError
                    class="mt-4"
                    :message="form.errors.permissions"
                />
            </div>
        </div>
    </form>
</template>