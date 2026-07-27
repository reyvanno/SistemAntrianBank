<script setup>
import Sidebar from '@/Components/Layout/Sidebar.vue'
import Navbar from '@/Components/Layout/Navbar.vue'
import FlashMassage from '@/Components/FlashMessage.vue'
</script>

<template>
    <div class="flex bg-slate-100 min-h-screen">

        <Sidebar />

        <div class="flex-1 flex flex-col">

            <Navbar />

            <main class="flex-1 p-8 overflow-y-auto">

                <FlashMassage />

                <slot />

            </main>

        </div>

    </div>
</template>