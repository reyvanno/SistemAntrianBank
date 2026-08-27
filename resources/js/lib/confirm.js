let resolveConfirm = null;

export function confirmDelete() {
    return new Promise((resolve) => {
        resolveConfirm = resolve;

        window.dispatchEvent(
            new CustomEvent("confirm-delete", {
                detail: {
                    title: "Hapus Data?",
                    message: "Data yang dihapus tidak dapat dikembalikan.",
                },
            }),
        );
    });
}

export function resolveDelete(value) {
    if (!resolveConfirm) return;

    resolveConfirm({
        isConfirmed: value,
    });

    resolveConfirm = null;
}
