<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

import {
    Squares2X2Icon,
    QueueListIcon,
    ComputerDesktopIcon,
    UsersIcon,
    ClipboardDocumentListIcon,
    BuildingOffice2Icon,
    ChartBarIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();

const user = computed(() => page.props.auth.user);

const role = computed(() => user.value.role.name);

const initials = computed(() => {
    return user.value.name
        .split(" ")
        .map((word) => word[0])
        .join("")
        .substring(0, 2)
        .toUpperCase();
});

const roleLabel = computed(() => {
    switch (role.value) {
        case "admin":
            return "Administrator";

        case "teller":
            return "Teller";

        case "customer_service":
            return "Customer Service";

        default:
            return role.value;
    }
});

const allMenus = [
    {
        title: "UTAMA",
        items: [
            {
                title: "Dashboard",
                route: "admin.dashboard",
                icon: Squares2X2Icon,
                roles: ["admin", "teller", "customer_service"],
            },
            {
                title: "Antrian",
                route: "admin.queues.index",
                icon: QueueListIcon,
                roles: ["admin", "teller"],
            },
            {
                title: "Monitor",
                route: "#",
                icon: ComputerDesktopIcon,
                roles: ["admin", "teller", "customer_service"],
            },
        ],
    },

    {
        title: "MASTER DATA",
        items: [
            {
                title: "Kelola User",
                route: "admin.users.index",
                icon: UsersIcon,
                roles: ["admin"],
            },
            {
                title: "Layanan",
                route: "admin.services.index",
                icon: ClipboardDocumentListIcon,
                roles: ["admin"],
            },
            {
                title: "Loket",
                route: "admin.counters.index",
                icon: BuildingOffice2Icon,
                roles: ["admin"],
            },
        ],
    },

    {
        title: "LAPORAN",
        items: [
            {
                title: "Reporting",
                route: "#",
                icon: ChartBarIcon,
                roles: ["admin"],
            },
        ],
    },
];

const menus = computed(() => {
    return allMenus
        .map((group) => ({
            ...group,
            items: group.items.filter((menu) =>
                menu.roles.includes(role.value)
            ),
        }))
        .filter((group) => group.items.length > 0);
});
</script>

<template>
    <aside class="w-72 bg-white border-r flex flex-col justify-between">
        <div>
            <!-- Logo -->

            <div class="border-b p-6">
                <h1 class="font-bold text-2xl">Antrian Bank</h1>

                <p class="text-gray-500">{{ roleLabel }} Panel</p>
            </div>

            <!-- Menu -->

            <div class="p-5">
                <div v-for="group in menus" :key="group.title" class="mb-8">
                    <p class="text-xs font-semibold text-gray-400 mb-4">
                        {{ group.title }}
                    </p>

                    <div v-for="menu in group.items" :key="menu.title">
                        <Link
                            v-if="menu.route !== '#'"
                            :href="route(menu.route)"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100 transition mb-2"
                        >
                            <component :is="menu.icon" class="w-6 h-6" />

                            <span class="font-medium">
                                {{ menu.title }}
                            </span>
                        </Link>

                        <div
                            v-else
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-500 mb-2"
                        >
                            <component :is="menu.icon" class="w-6 h-6" />

                            {{ menu.title }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->

        <div class="border-t p-5">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold"
                >
                    {{ initials }}
                </div>

                <div>
                    <h3 class="font-semibold">{{ user.name }}</h3>

                    <p class="text-gray-500 text-sm">{{ roleLabel }}</p>
                </div>
            </div>
        </div>
    </aside>
</template>
