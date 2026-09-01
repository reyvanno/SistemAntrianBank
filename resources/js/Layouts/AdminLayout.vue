<script setup>
import Sidebar from "@/Components/Layout/Sidebar.vue";
import Navbar from "@/Components/Layout/Navbar.vue";
import FlashMessage from "@/Components/FlashMessage.vue";

import axios from "axios";

import {
    computed,
    onBeforeUnmount,
    onMounted,
} from "vue";

import { usePage } from "@inertiajs/vue3";

const page = usePage();

const user = computed(() => {
    return page.props.auth?.user;
});

const isAdmin = computed(() => {
    return user.value?.role === "admin";
});

/*
|--------------------------------------------------------------------------
| Counter Presence
|--------------------------------------------------------------------------
*/

let heartbeatTimer = null;

const sendHeartbeat = async () => {
    if (isAdmin.value) {
        return;
    }

    if (!user.value?.id) {
        return;
    }

    if (!user.value?.counter_id) {
        return;
    }

    try {
        const response = await axios.post(
            route("admin.counter.heartbeat"),
            {},
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },

                withCredentials: true,
            },
        );

        console.log(
            "[Presence] Heartbeat berhasil:",
            response.data,
        );
    } catch (error) {
        console.error(
            "[Presence] Heartbeat gagal:",
            error.response?.status,
            error.response?.data ?? error,
        );
    }
};

const sendOffline = () => {
    if (isAdmin.value) {
        return;
    }

    if (!user.value?.id) {
        return;
    }

    if (!user.value?.counter_id) {
        return;
    }

    const csrfToken =
        document
            .querySelector(
                'meta[name="csrf-token"]',
            )
            ?.getAttribute("content");

    if (!csrfToken) {
        console.error(
            "[Presence] CSRF token tidak ditemukan.",
        );

        return;
    }

    const data = new URLSearchParams();

    data.append(
        "_token",
        csrfToken,
    );

    navigator.sendBeacon(
        route("admin.counter.offline"),
        data,
    );
};

/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

onMounted(() => {
    if (isAdmin.value) {
        return;
    }

    if (!user.value?.counter_id) {
        return;
    }

    /*
     * Kirim langsung.
     */
    sendHeartbeat();

    /*
     * Ulangi setiap 30 detik.
     */
    heartbeatTimer = window.setInterval(
        sendHeartbeat,
        30 * 1000,
    );

    window.addEventListener(
        "pagehide",
        sendOffline,
    );
});

/*
|--------------------------------------------------------------------------
| Unmount
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
    if (heartbeatTimer) {
        window.clearInterval(
            heartbeatTimer,
        );

        heartbeatTimer = null;
    }

    window.removeEventListener(
        "pagehide",
        sendOffline,
    );
});
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-slate-100">
        <aside class="h-screen shrink-0 overflow-y-auto">
            <Sidebar />
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <Navbar />

            <main class="min-h-0 flex-1 overflow-y-auto">
                <div class="p-8">
                    <FlashMessage />

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>