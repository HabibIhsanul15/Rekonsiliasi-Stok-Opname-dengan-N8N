interface BadgeProps {
    children: React.ReactNode;
    variant?: 'green' | 'yellow' | 'red' | 'blue' | 'gray' | 'orange';
}

const variantClasses: Record<string, string> = {
    green: 'badge-green',
    yellow: 'badge-yellow',
    red: 'badge-red',
    blue: 'badge-blue',
    gray: 'badge-gray',
    orange: 'badge-orange',
};

export default function Badge({ children, variant = 'gray' }: BadgeProps) {
    return (
        <span className={`badge ${variantClasses[variant] || 'badge-gray'}`}>
            {children}
        </span>
    );
}

// Helper to convert severity to badge variant
export function severityVariant(severity: string): BadgeProps['variant'] {
    const map: Record<string, BadgeProps['variant']> = {
        low: 'green',
        medium: 'yellow',
        high: 'orange',
        critical: 'red',
    };
    return map[severity] || 'gray';
}

// Helper to convert status to badge variant
export function statusVariant(status: string): BadgeProps['variant'] {
    const map: Record<string, BadgeProps['variant']> = {
        auto_approved: 'green',
        approved: 'green',
        pending: 'yellow',
        rejected: 'gray',
        escalated: 'red',
        completed: 'green',
        in_progress: 'blue',
        draft: 'gray',
        processing: 'blue',
        failed: 'red',
    };
    return map[status] || 'gray';
}

// Indonesian labels
export const severityLabels: Record<string, string> = {
    low: 'Rendah',
    medium: 'Sedang',
    high: 'Tinggi',
    critical: 'Kritis',
};

export const statusLabels: Record<string, string> = {
    auto_approved: 'Otomatis',
    pending: 'Menunggu',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    escalated: 'Eskalasi',
    draft: 'Draf',
    in_progress: 'Sedang Berjalan',
    completed: 'Selesai',
    closed: 'Ditutup',
    processing: 'Proses',
    failed: 'Gagal',
};
