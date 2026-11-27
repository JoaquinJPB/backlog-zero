// Nested groups from lang/<locale>/ui.php, shared once by HandleInertiaRequests.
export interface Translations {
    [key: string]: string | Translations;
}
