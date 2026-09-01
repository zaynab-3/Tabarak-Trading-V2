export const formatDate = (value?: string | null): string =>
    value ? new Intl.DateTimeFormat('en-LB', { dateStyle: 'medium' }).format(new Date(value)) : '—';

export const humanFileSize = (bytes = 0): string => {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

export const productPackLabel = (product: { weight_value: string | number | null; weight_unit: string | null; pack_quantity: number | null }): string => {
    const weight = product.weight_value && product.weight_unit ? `${Number(product.weight_value).toLocaleString()} ${product.weight_unit}` : null;
    const pack = product.pack_quantity ? `case of ${product.pack_quantity}` : null;
    return [weight, pack].filter(Boolean).join(' · ') || 'Wholesale format';
};

export const initials = (value: string): string => value.split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
