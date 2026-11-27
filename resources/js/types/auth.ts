// Mirrors the `auth.user` shared prop built in HandleInertiaRequests::share().
export interface User {
    id: number;
    name: string;
    username: string | null;
    avatar_url: string | null;
}

export interface Auth {
    user: User | null;
}
