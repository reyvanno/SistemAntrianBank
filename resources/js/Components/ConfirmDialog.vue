    <script setup>
    import { onMounted, onUnmounted, ref } from "vue";
    import { resolveDelete } from "@/lib/confirm";

    const visible = ref(false);

    const title = ref("");
    const message = ref("");

    const show = (event) => {
        title.value = event.detail.title;
        message.value = event.detail.message;
        visible.value = true;
    };

    const close = (confirmed) => {
        visible.value = false;

        resolveDelete(confirmed);
    };

    onMounted(() => {
        window.addEventListener("confirm-delete", show);
    });

    onUnmounted(() => {
        window.removeEventListener("confirm-delete", show);
    });
    </script>

    <template>
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="visible"
                    class="fixed inset-0 z-[9998] flex items-center justify-center bg-slate-900/40 px-4 backdrop-blur-sm"
                >
                    <div
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600"
                            >
                                <span class="text-xl font-bold">
                                    !
                                </span>
                            </div>

                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-800"
                                >
                                    {{ title }}
                                </h2>

                                <p
                                    class="mt-1 text-sm leading-relaxed text-slate-500"
                                >
                                    {{ message }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-6 flex justify-end gap-3"
                        >
                            <button
                                type="button"
                                @click="close(false)"
                                class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                            >
                                Batal
                            </button>

                            <button
                                type="button"
                                @click="close(true)"
                                class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700"
                            >
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </template>