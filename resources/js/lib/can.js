import { usePage } from "@inertiajs/vue3";

export function can(permission) {
    const permissions = usePage().props.auth.user?.permissions ?? [];

    return permissions.includes(permission);
}

export function canAny(permissions) {
    return permissions.some((permission) => can(permission));
}

export function canAll(permissions) {
    return permissions.every((permission) => can(permission));
}
