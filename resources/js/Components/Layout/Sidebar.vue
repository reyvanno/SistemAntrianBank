<script setup>
import { computed, ref } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import {
    BuildingLibraryIcon,
    Squares2X2Icon,
    QueueListIcon,
    ComputerDesktopIcon,
    UsersIcon,
    ClipboardDocumentListIcon,
    BuildingOffice2Icon,
    ChartBarIcon,
    ChevronUpIcon,
    ArrowRightOnRectangleIcon,
    UserCircleIcon,
    ShieldCheckIcon,
} from "@heroicons/vue/24/outline";
import { can } from "@/lib/can";

const page = usePage();

const user = computed(() => page.props.auth.user);

const role = computed(() => user.value.role);

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
                permission: "dashboard.view",
            },
            {
                title: "Antrian",
                route: "admin.queues.index",
                icon: QueueListIcon,
                permission: "queue.view",
            },
            {
                title: "Monitor",
                route: "monitor",
                icon: ComputerDesktopIcon,
                permission: "monitor.view",
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
                permission: "user.view",
            },
            {
                title: "Layanan",
                route: "admin.services.index",
                icon: ClipboardDocumentListIcon,
                permission: "service.view",
            },
            {
                title: "Loket",
                route: "admin.counters.index",
                icon: BuildingOffice2Icon,
                permission: "counter.view",
            },
            {
                title: "Role Management",
                route: "admin.roles.index",
                icon: ShieldCheckIcon,
                permission: "role.view"
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
                permission: "report.view",
            },
        ],
    },
];

const menus = computed(() => {
    return allMenus
        .map((group) => ({
            ...group,
            items: group.items.filter((menu) => can(menu.permission)),
        }))
        .filter((group) => group.items.length > 0);
});

const isActive = (routeName) => {
    return routeName !== "#" && route().current(routeName);
};

const profileOpen = ref(false);

const logout = () => {
    router.post(route("logout"));
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
        <div class="relative border-t border-slate-200 bg-white p-4">
            <button
                @click="profileOpen = !profileOpen"
                class="flex w-full items-center gap-3 rounded-xl p-2 transition hover:bg-slate-100"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white"
                >
                    {{ initials }}
                </div>

                <div class="min-w-0 flex-1 text-left">
                    <h3 class="truncate font-semibold text-slate-800">
                        {{ user.name }}
                    </h3>

                    <p class="truncate text-sm text-slate-500">
                        {{ roleLabel }}
                    </p>
                </div>

                <ChevronUpIcon
                    class="h-5 w-5 text-slate-400 transition duration-200"
                    :class="{ 'rotate-180': profileOpen }"
                />
            </button>

            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-2"
            >
                <div
                    v-if="profileOpen"
                    class="absolute bottom-20 left-4 right-4 rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
                >
                    <button
                        disabled
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100"
                    >
                        <UserCircleIcon class="h-5 w-5" />
                        Profile
                    </button>

                    <button
                        @click="logout"
                        class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-600 transition hover:bg-red-50"
                    >
                        <ArrowRightOnRectangleIcon class="h-5 w-5" />
                        Logout
                    </button>
                </div>
            </Transition>
        </div>
    </aside>
</template>
