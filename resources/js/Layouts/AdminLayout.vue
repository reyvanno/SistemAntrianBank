<script setup>
import Sidebar from "@/Components/Layout/Sidebar.vue";
import Navbar from "@/Components/Layout/Navbar.vue";
import FlashMessage from "@/Components/FlashMessage.vue";
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-slate-100">
        <!-- SIDEBAR -->
        <aside class="h-screen shrink-0 overflow-y-auto">
            <Sidebar />
        </aside>

        <!-- MAIN AREA -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- NAVBAR -->
            <Navbar />

            <!-- CONTENT -->
            <main class="min-h-0 flex-1 overflow-y-auto">
                <div class="p-8">
                    <FlashMessage />

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>