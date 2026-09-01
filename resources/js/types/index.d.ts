import type { CartSummary } from './orders';

export interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    flash: {
        success?: string;
        error?: string;
    };
    cart: CartSummary;
};
