import Swal from "sweetalert2";

const base = {
    confirmButtonColor: "#4F46E5",
    cancelButtonColor: "#6B7280",
    reverseButtons: true,
    allowOutsideClick: false,
};

export function success(message) {
    return Swal.fire({
        ...base,
        icon: "success",
        title: "Berhasil",
        text: message,
    });
}

export function error(message) {
    return Swal.fire({
        ...base,
        icon: "error",
        title: "Terjadi Kesalahan",
        text: message,
    });
}

export function warning(message) {
    return Swal.fire({
        ...base,
        icon: "warning",
        title: "Peringatan",
        text: message,
    });
}

export function confirmDelete() {
    return Swal.fire({
        ...base,

        icon: "warning",

        title: "Hapus Data?",

        text: "Data yang dihapus tidak dapat dikembalikan.",

        showCancelButton: true,

        confirmButtonText: "Ya, Hapus",

        cancelButtonText: "Batal",
    });
}