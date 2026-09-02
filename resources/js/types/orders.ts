import type { Product } from '@/types/catalogue';

export interface CartItem {
    product: Product;
    quantity: number;
    unit_price: string;
    line_total: string;
}

export interface CartSummary {
    items: CartItem[];
    item_count: number;
    subtotal: string;
    currency: 'USD';
}

export interface OrderItem {
    id: number;
    product_id: number | null;
    product_name: string;
    product_sku: string | null;
    pack_label: string | null;
    image_url: string | null;
    image_alt_text: string | null;
    unit_price: string;
    quantity: number;
    stock_reserved: number;
    line_total: string;
}

export interface Order {
    id: number;
    public_token: string;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    status: 'pending' | 'completed';
    currency: 'USD';
    subtotal: string;
    total: string;
    items_count: number;
    reserved_stock_quantity: number;
    submitted_at: string;
    completed_at: string | null;
    items?: OrderItem[];
}

export type OrderDeletionMode = 'cancel_restore_stock' | 'delete_record_only';

export interface DeletedOrderItem {
    id: number;
    product_id: number | null;
    product_name: string;
    product_sku: string | null;
    pack_label: string | null;
    image_alt_text: string | null;
    image_url: string | null;
    unit_price: string;
    quantity: number;
    stock_reserved: number;
    stock_restored: number;
    line_total: string;
}

export interface OrderDeletionNotice {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    order_status: 'pending' | 'completed';
    currency: 'USD';
    subtotal: string;
    total: string;
    deletion_mode: OrderDeletionMode;
    restored_quantity: number;
    items_count: number;
    submitted_at: string;
    completed_at: string | null;
    recorded_at: string;
    deleted_by: string | null;
    items?: DeletedOrderItem[];
}

export interface OrderFilters {
    search?: string;
    status?: string;
}
