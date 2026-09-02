import { router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

export interface ProductFilters {
    search?: string;
    category?: number | string;
    brand?: number | string;
    status?: string;
    sort?: string;
}

export function useProductFilters(path: string, initial: ProductFilters, defaultOptions?: { preserveScroll?: boolean }) {
    const initialFilters = { ...initial } as ProductFilters & { page?: number };
    delete initialFilters.page;
    const filters = reactive<ProductFilters>({ search: '', category: '', brand: '', status: '', sort: 'newest', ...initialFilters });

    const apply = (options?: { preserveScroll?: boolean }) => {
        const preserveScroll = options?.preserveScroll ?? defaultOptions?.preserveScroll ?? false;
        router.get(
            path,
            { ...filters },
            {
                preserveState: true,
                preserveScroll,
                replace: true,
                onSuccess: () => {
                    if (!preserveScroll) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
            },
        );
    };

    const reset = () => {
        Object.assign(filters, { search: '', category: '', brand: '', status: '', sort: 'newest' });
        apply({ preserveScroll: false });
    };

    watch(
        () => initial,
        (next) => {
            if (next) {
                const clean = { ...next } as ProductFilters & { page?: number };
                delete clean.page;
                Object.assign(filters, clean);
            }
        },
        { deep: true },
    );

    return { filters, apply, reset };
}
