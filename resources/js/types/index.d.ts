export interface User {
    id: number;
    name: string;
    email: string;
}

export interface PageProps {
    [key: string]: unknown;
    auth: {
        user: User | null;
    };
    flash: {
        success: string | null;
        error: string | null;
    };
}

export interface Item {
    id: number;
    item_code: string;
    name: string;
    jenis_barang: string;
    kategori_barang: string | null;
    unit: string;
}

export interface OpnameSession {
    id: number;
    session_code: string;
    opname_date: string;
    status: 'draft' | 'in_progress' | 'completed' | 'closed';
    notes: string | null;
    started_at: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    entries: OpnameEntry[];
    conductor?: User;
}

export interface OpnameEntry {
    id: number;
    opname_session_id: number;
    item_id: number;
    system_qty: number;
    counted_qty: number;
    variance: number;
    variance_pct: number;
    notes: string | null;
    item: Item;
    varianceReview: VarianceReview | null;
}

export interface VarianceReview {
    id: number;
    opname_entry_id: number;
    status: 'auto_approved' | 'pending' | 'approved' | 'rejected' | 'escalated';
    severity: 'low' | 'medium' | 'high' | 'critical';
    auto_resolved: boolean;
    notes: string | null;
    reviewed_at: string | null;
    reviewer?: User | null;
    opnameEntry: OpnameEntry;
}

export interface OpnameImport {
    id: number;
    file_name: string;
    total_rows: number;
    imported_rows: number;
    failed_rows: number;
    status: 'completed' | 'processing' | 'failed';
    errors: Array<{ row: number; message: string }> | null;
    created_at: string;
    session: OpnameSession | null;
    uploader?: User;
}

export interface ActivityLog {
    id: number;
    action: string;
    created_at: string;
    user: User | null;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
