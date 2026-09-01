import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';

export function useImportBatchPolling(isActive: () => boolean, intervalMs = 2500) {
    let timer: ReturnType<typeof setTimeout> | undefined;

    const schedule = () => {
        if (!isActive()) {
            return;
        }

        timer = setTimeout(() => {
            if (!isActive()) {
                return;
            }

            router.reload({
                only: ['batch'],
                onFinish: schedule,
            });
        }, intervalMs);
    };

    onMounted(schedule);
    onBeforeUnmount(() => timer && clearTimeout(timer));
}
