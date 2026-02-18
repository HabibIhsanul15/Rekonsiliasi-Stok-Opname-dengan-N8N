import { ReactNode } from 'react';

interface StatCardProps {
    label: string;
    value: string | number;
    color?: string;
    icon?: ReactNode;
    accent?: string;
}

export default function StatCard({ label, value, color = 'text-white', icon, accent }: StatCardProps) {
    return (
        <div
            className="stat-card group"
            style={{ '--card-accent': accent || '#6366f1' } as React.CSSProperties}
        >
            {/* Top accent line (visible on hover via CSS) */}
            <div className="flex items-start justify-between">
                <div>
                    <p className="text-[10px] font-bold text-slate-500 uppercase tracking-[0.15em] mb-2">{label}</p>
                    <p className={`text-3xl font-extrabold tracking-tight ${color}`}>{value}</p>
                </div>
                {icon && (
                    <div className="w-10 h-10 rounded-xl bg-white/[0.03] border border-white/[0.06] flex items-center justify-center text-lg opacity-60 group-hover:opacity-100 transition-opacity">
                        {icon}
                    </div>
                )}
            </div>
        </div>
    );
}
