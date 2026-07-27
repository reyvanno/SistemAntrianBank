<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    BuildingLibraryIcon,
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
                menu.roles.includes(role.value),
            ),
        }))
        .filter((group) => group.items.length > 0);
});

const isActive = (routeName) => {
    return routeName !== "#" && route().current(routeName);
};
</script>

<template>
    <aside class="flex w-64 flex-col border-r border-slate-200 bg-white">
        <!-- Header -->
        <div class="border-b border-slate-200 px-6 py-6">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100"
                >
                    <BuildingLibraryIcon class="h-7 w-7 text-indigo-600" />
                </div>

                <div>
                    <h1 class="text-lg font-bold text-slate-800">
                        Sistem Antrian
                    </h1>

                    <p class="text-sm text-slate-500">
                        {{ roleLabel }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Menu -->
        <div class="flex-1 px-5 py-6">
            <div v-for="group in menus" :key="group.title" class="mb-8">
                <p
                    class="mb-3 px-2 text-xs font-bold uppercase tracking-widest text-slate-400"
                >
                    {{ group.title }}
                </p>

                <div v-for="menu in group.items" :key="menu.title">
                    <Link
                        v-if="menu.route !== '#'"
                        :href="route(menu.route)"
                        class="mb-2 flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200"
                        :class="
                            isActive(menu.route)
                                ? 'bg-indigo-50 font-semibold text-indigo-600'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        "
                    >
                        <component :is="menu.icon" class="h-5 w-5" />

                        <span>
                            {{ menu.title }}
                        </span>
                    </Link>

                    <div
                        v-else
                        class="mb-2 flex cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3 text-slate-400 opacity-70"
                    >
                        <component :is="menu.icon" class="h-5 w-5" />

                        <span>{{ menu.title }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile -->
        <div class="border-t border-slate-200 bg-white">
            <div
                class="flex items-center gap-3 px-5 py-5 transition-colors hover:bg-slate-50"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white"
                >
                    {{ initials }}
                </div>

                <div class="min-w-0">
                    <h3 class="truncate font-semibold text-slate-800">
                        {{ user.name }}
                    </h3>

                    <p class="truncate text-sm text-slate-500">
                        {{ roleLabel }}
                    </p>
                </div>
            </div>
        </div>
    </aside>
</template>
