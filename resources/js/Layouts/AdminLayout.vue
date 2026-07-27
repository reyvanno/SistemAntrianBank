<script setup>
import Sidebar from "@/Components/Layout/Sidebar.vue";
import Navbar from "@/Components/Layout/Navbar.vue";
import FlashMessage from "@/Components/FlashMessage.vue";
</script>

<template>
    <div class="flex min-h-screen bg-slate-100">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Navbar />

            <main class="flex-1">
                <div class="p-8">
                    <FlashMessage />

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>