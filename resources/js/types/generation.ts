export type GenerationStatus = 'pending' | 'queued' | 'processing' | 'succeeded' | 'failed';

export interface Generation {
    id: number;
    prompt: string;
    size: string;
    display_size: string;
    quality: string;
    display_quality: string;
    status: GenerationStatus;
    display_status: string;
    is_finished: boolean;
    reference_images: string[];
    estimated_typical_price: string;
    estimated_maximum_price: string;
    actual_price: string | null;
    image_url: string | null;
    has_expired_image: boolean;
    error_message: string | null;
    created_at: string;
    completed_at: string | null;
}

export interface CostBreakdown {
    rows: { token_type: string; tokens: number; cost: string }[];
    provider_cost: string;
    markup: string;
    total: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    meta: { current_page: number; last_page: number; total: number; links: PaginationLink[] };
}
