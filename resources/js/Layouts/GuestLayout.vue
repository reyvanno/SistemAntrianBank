<script setup>
import Navbar from "@/Components/Layout/Navbar.vue";
import AplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-100">
        <Navbar :guest="true" />

        <main class="min-h-0 flex-1 overflow-y-auto">
            <div
                class="flex min-h-full items-center justify-center px-5 py-10 sm:px-8"
            >
                <slot />
            </div>
        </main>
         <!-- FOOTER -->
        <footer
            class="shrink-0 border-t border-slate-200 bg-white"
        >
            <div
                class="flex min-h-[64px] items-center justify-center px-5"
            >
                <p
                    class="text-center text-xs text-slate-500 sm:text-sm"
                >
                    &copy; {{ currentYear }}
                    Sistem Antrian Bank. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>