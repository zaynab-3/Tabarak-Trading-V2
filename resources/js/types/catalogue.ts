export interface MediaItem {
    id: number;
    url: string;
    alt_text: string | null;
    width?: number | null;
    height?: number | null;
    original_name?: string;
    mime_type?: string;
    size?: number;
    product_images_count?: number;
    import_items_count?: number;
}

export interface TaxonomyRef {
    id: number;
    name: string;
    slug?: string;
}

export interface ProductImage {
    id: number;
    sort_order: number;
    is_primary: boolean;
    media: MediaItem;
}

export interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string | null;
    short_description: string | null;
    description?: string | null;
    weight_value: string | number | null;
    weight_unit: string | null;
    pack_quantity: number | null;
    unit_label: string | null;
    status: 'draft' | 'published' | 'archived';
    is_featured: boolean;
    updated_at?: string;
    brand: TaxonomyRef | null;
    category: TaxonomyRef | null;
    primary_image: MediaItem | null;
    images?: ProductImage[];
    variants?: Array<Record<string, unknown>>;
}

export interface Category extends TaxonomyRef {
    description: string | null;
    parent_id?: number | null;
    parent?: TaxonomyRef | null;
    image_id?: number | null;
    image?: MediaItem | null;
    sort_order: number;
    is_active: boolean;
    products_count: number;
}

export interface Brand extends TaxonomyRef {
    description: string | null;
    logo_image_id?: number | null;
    logo?: MediaItem | null;
    is_active: boolean;
    products_count: number;
}

export interface PaginationLink { url: string | null; label: string; active: boolean; }
export interface Paginated<T> { data: T[]; links: PaginationLink[]; current_page: number; last_page: number; from: number | null; to: number | null; total: number; }

export interface ImportItem {
    id: number;
    status: string;
    suggested_name: string | null;
    suggested_brand: string | null;
    suggested_category: string | null;
    suggested_weight: string | null;
    confidence: string | null;
    warnings: string[] | null;
    media: MediaItem;
}

export interface ImportBatch {
    id: number;
    name: string | null;
    status: string;
    total_items: number;
    processed_items: number;
    approved_items: number;
    rejected_items: number;
    created_at: string;
    creator?: { id: number; name: string } | null;
    items?: ImportItem[];
}
