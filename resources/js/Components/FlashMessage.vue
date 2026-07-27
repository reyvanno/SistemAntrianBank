<script setup>
import { watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { success, error } from "@/lib/swal";

const page = usePage();

watch(
    () => page.props.flash,

    (flash) => {

        if (!flash) return;

        if (flash.success) {
            success(flash.success);
        }

        if (flash.error) {
            error(flash.error);
        }

    },

    {
        immediate: true,
    }
);
</script>

<template></template>