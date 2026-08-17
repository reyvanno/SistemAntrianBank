export function roleLabel(role) {
    switch (role) {
        case "admin":
            return "Administrator";

        case "teller":
            return "Teller";

        case "customer_service":
            return "Customer Service";

        default:
            return role;
    }
}
