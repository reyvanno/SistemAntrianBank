<script setup>
import { watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import {
    success,
    error,
    warning,
} from "@/lib/toast";

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

        if (flash.warning) {
            warning(flash.warning);
        }
    },
    {
        immediate: true,
    },
);
</script>

<template></template>