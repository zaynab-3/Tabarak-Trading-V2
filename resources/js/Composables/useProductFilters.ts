import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

export interface ProductFilters {
    search?: string;
    category?: number | string;
    brand?: number | string;
    status?: string;
    sort?: string;
}

export function useProductFilters(path: string, initial: ProductFilters) {
    const filters = reactive<ProductFilters>({ search: '', category: '', brand: '', status: '', sort: 'newest', ...initial });

    const apply = () => router.get(path, { ...filters }, { preserveState: true, preserveScroll: true, replace: true });
    const reset = () => {
        Object.assign(filters, { search: '', category: '', brand: '', status: '', sort: 'newest' });
        apply();
    };

    return { filters, apply, reset };
}
