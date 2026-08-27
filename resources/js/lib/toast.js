let toastId = 0;

export function showToast(type, message) {
    const id = ++toastId;

    window.dispatchEvent(
        new CustomEvent("show-toast", {
            detail: {
                id,
                type,
                message,
            },
        }),
    );
}

export function success(message) {
    showToast("success", message);
}

export function error(message) {
    showToast("error", message);
}

export function warning(message) {
    showToast("warning", message);
}
