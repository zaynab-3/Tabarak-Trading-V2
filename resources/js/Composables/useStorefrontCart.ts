import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import type { CartSummary } from '@/types/orders';

export function useStorefrontCart() {
    const page = usePage<PageProps>();

    const syncCart = (cart: CartSummary) => {
        Object.assign(page.props.cart, cart);
    };

    return { syncCart };
}
