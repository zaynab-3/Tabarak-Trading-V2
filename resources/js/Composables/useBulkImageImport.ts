import axios, { AxiosError } from 'axios';
import { computed, ref } from 'vue';

interface CreateBatchResponse {
    batch_id: number;
    upload_url: string;
    show_url: string;
}

interface ValidationPayload {
    message?: string;
    errors?: Record<string, string[]>;
}

export function useBulkImageImport(chunkSize: number, maxImageSizeMb: number) {
    const name = ref('');
    const files = ref<File[]>([]);
    const uploaded = ref(0);
    const processing = ref(false);
    const error = ref<string | null>(null);
    const batchUrl = ref<string | null>(null);

    const progress = computed(() => {
        if (files.value.length === 0) {
            return 0;
        }

        return Math.round((uploaded.value / files.value.length) * 100);
    });

    const setFiles = (selection: File[]) => {
        error.value = null;
        const allowedTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
        const invalidType = selection.find((file) => !allowedTypes.has(file.type));
        const tooLarge = selection.find((file) => file.size > maxImageSizeMb * 1024 * 1024);

        if (invalidType) {
            files.value = [];
            error.value = `${invalidType.name} is not a JPG, PNG, or WebP image.`;

            return;
        }

        if (tooLarge) {
            files.value = [];
            error.value = `${tooLarge.name} is larger than ${maxImageSizeMb} MB.`;

            return;
        }

        files.value = selection;
        uploaded.value = 0;
    };

    const submit = async () => {
        if (processing.value || files.value.length === 0) {
            return;
        }

        processing.value = true;
        error.value = null;
        uploaded.value = 0;

        try {
            const created = await axios.post<CreateBatchResponse>(
                route('admin.imports.store'),
                { name: name.value || null },
                { headers: { Accept: 'application/json' } },
            );
            const { upload_url: uploadUrl, show_url: showUrl } = created.data;
            batchUrl.value = showUrl;

            for (let index = 0; index < files.value.length; index += chunkSize) {
                const chunk = files.value.slice(index, index + chunkSize);
                const body = new FormData();

                chunk.forEach((file) => body.append('images[]', file));
                await axios.post(uploadUrl, body, { headers: { Accept: 'application/json' } });
                uploaded.value += chunk.length;
            }

            window.location.assign(showUrl);
        } catch (caught) {
            error.value = errorMessage(caught);
            processing.value = false;
        }
    };

    return {
        batchUrl,
        error,
        files,
        name,
        processing,
        progress,
        setFiles,
        submit,
        uploaded,
    };
}

function errorMessage(caught: unknown): string {
    if (caught instanceof AxiosError) {
        const payload = caught.response?.data as ValidationPayload | undefined;
        const firstValidationMessage = payload?.errors
            ? Object.values(payload.errors).flat().find(Boolean)
            : null;

        return firstValidationMessage || payload?.message || 'The upload could not be completed.';
    }

    return 'The upload could not be completed.';
}
