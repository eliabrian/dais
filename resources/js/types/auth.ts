export type User = {
    id: string;
    username: string;
    email: string;
    avatar_url?: string | null;
    theme?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
