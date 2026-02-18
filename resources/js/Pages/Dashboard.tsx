import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import StatCard from '@/Components/StatCard';
import Badge, { statusLabels, statusVariant, severityLabels } from '@/Components/Badge';
import { OpnameEntry, ActivityLog } from '@/types';
import { PieChart, Pie, Cell, ResponsiveContainer, Legend, Tooltip } from 'recharts';

interface DashboardProps {
    activeSessions: number;
    totalEntries: number;
    totalVariances: number;
    pendingReviews: number;
    autoApproved: number;
    varianceDistribution: Record<string, number>;
    statusDistribution: Record<string, number>;
    recentActivity: ActivityLog[];
    topDiscrepancies: OpnameEntry[];
}

const SEVERITY_COLORS: Record<string, string> = {
    low: '#10B981',
    medium: '#F59E0B',
    high: '#EF4444',
    critical: '#8B5CF6',
};

const STATUS_COLORS: Record<string, string> = {
    auto_approved: '#10B981',
    pending: '#3B82F6',
    approved: '#22C55E',
    rejected: '#EF4444',
    escalated: '#F59E0B',
};

const SEVERITY_LABELS: Record<string, string> = {
    low: 'Rendah',
    medium: 'Sedang',
    high: 'Tinggi',
    critical: 'Kritis',
};

const STATUS_LABELS: Record<string, string> = {
    auto_approved: 'Otomatis',
    pending: 'Menunggu',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    escalated: 'Eskalasi',
};

function toChartData(data: Record<string, number>, colors: Record<string, string>, labels: Record<string, string>) {
    return Object.entries(data).map(([key, value]) => ({
        name: labels[key] || key.replace('_', ' '),
        value,
        color: colors[key] || '#64748B',
    }));
}

const tooltipStyle = {
    background: 'rgba(10, 15, 30, 0.95)',
    border: '1px solid rgba(255,255,255,0.08)',
    borderRadius: '12px',
    color: '#e2e8f0',
    fontSize: '12px',
    boxShadow: '0 8px 32px rgba(0,0,0,0.3)',
};

export default function Dashboard({
    activeSessions, totalEntries, totalVariances,
    pendingReviews, autoApproved,
    varianceDistribution, statusDistribution,
    recentActivity, topDiscrepancies,
}: DashboardProps) {
    const severityChartData = toChartData(varianceDistribution, SEVERITY_COLORS, SEVERITY_LABELS);
    const statusChartData = toChartData(statusDistribution, STATUS_COLORS, STATUS_LABELS);

    return (
        <AppLayout header={
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-xl font-bold text-white tracking-tight">Dasbor</h2>
                    <p className="text-xs text-slate-600 mt-0.5">Ringkasan aktivitas stock opname</p>
                </div>
                <div className="text-[10px] text-slate-600 font-medium tracking-wide">
                    {new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                </div>
            </div>
        }>
            <Head title="Dasbor" />

            <div className="max-w-7xl mx-auto space-y-6">
                {/* ═══ Summary Cards ═══ */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    {[
                        { label: 'Import Aktif', value: activeSessions, color: 'text-blue-400', accent: '#3B82F6' },
                        { label: 'Total Item', value: totalEntries, color: 'text-white', accent: '#6366f1' },
                        { label: 'Selisih Ditemukan', value: totalVariances, color: 'text-orange-400', accent: '#F97316' },
                        { label: 'Menunggu Review', value: pendingReviews, color: 'text-red-400', accent: '#EF4444' },
                        { label: 'Otomatis Disetujui', value: autoApproved, color: 'text-emerald-400', accent: '#10B981' },
                    ].map((card, idx) => (
                        <div key={card.label} className="animate-fade-in-up" style={{ animationDelay: `${idx * 0.08}s` }}>
                            <StatCard {...card} value={card.value.toLocaleString('id-ID')} />
                        </div>
                    ))}
                </div>

                {/* ═══ Charts ═══ */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Severity Distribution */}
                    <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.3s' }}>
                        <h3 className="section-title mb-5">
                            <span className="section-dot bg-indigo-500 text-indigo-500" /> Distribusi Keparahan
                        </h3>
                        {severityChartData.length > 0 ? (
                            <ResponsiveContainer width="100%" height={260}>
                                <PieChart>
                                    <Pie data={severityChartData} cx="50%" cy="50%" innerRadius={65} outerRadius={95} dataKey="value" paddingAngle={4} strokeWidth={0}>
                                        {severityChartData.map((entry, i) => (
                                            <Cell key={i} fill={entry.color} />
                                        ))}
                                    </Pie>
                                    <Tooltip contentStyle={tooltipStyle} />
                                    <Legend wrapperStyle={{ color: '#64748b', fontSize: '11px', fontWeight: 500 }} />
                                </PieChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="flex items-center justify-center h-[260px] text-slate-600 text-sm">Belum ada data</div>
                        )}
                    </div>

                    {/* Status Distribution */}
                    <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.4s' }}>
                        <h3 className="section-title mb-5">
                            <span className="section-dot bg-purple-500 text-purple-500" /> Status Review
                        </h3>
                        {statusChartData.length > 0 ? (
                            <ResponsiveContainer width="100%" height={260}>
                                <PieChart>
                                    <Pie data={statusChartData} cx="50%" cy="50%" innerRadius={65} outerRadius={95} dataKey="value" paddingAngle={4} strokeWidth={0}>
                                        {statusChartData.map((entry, i) => (
                                            <Cell key={i} fill={entry.color} />
                                        ))}
                                    </Pie>
                                    <Tooltip contentStyle={tooltipStyle} />
                                    <Legend wrapperStyle={{ color: '#64748b', fontSize: '11px', fontWeight: 500 }} />
                                </PieChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="flex items-center justify-center h-[260px] text-slate-600 text-sm">Belum ada data</div>
                        )}
                    </div>
                </div>

                {/* ═══ Top Discrepancies ═══ */}
                <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.5s' }}>
                    <div className="p-5 border-b border-white/[0.04]">
                        <h3 className="section-title">
                            <span className="section-dot bg-red-500 text-red-500" /> 10 Item Selisih Terbesar
                        </h3>
                    </div>
                    {topDiscrepancies.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th className="text-right">Sistem</th>
                                        <th className="text-right">Fisik</th>
                                        <th className="text-right">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {topDiscrepancies.map(entry => (
                                        <tr key={entry.id}>
                                            <td>
                                                <span className="font-semibold text-slate-200">{entry.item?.name ?? '-'}</span>
                                                <span className="block text-[10px] text-slate-600 mt-0.5">{entry.item?.item_code ?? '-'}</span>
                                            </td>
                                            <td className="text-right text-slate-400 tabular-nums">{Number(entry.system_qty).toFixed(2)}</td>
                                            <td className="text-right text-slate-300 font-medium tabular-nums">{Number(entry.counted_qty).toFixed(2)}</td>
                                            <td className={`text-right font-bold tabular-nums ${Number(entry.variance) < 0 ? 'text-red-400' : Number(entry.variance) > 0 ? 'text-emerald-400' : 'text-slate-600'}`}>
                                                {Number(entry.variance) > 0 ? '+' : ''}{Number(entry.variance).toFixed(2)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="p-8 text-center text-slate-600 text-sm">Belum ada data selisih</div>
                    )}
                </div>

                {/* ═══ Recent Activity ═══ */}
                <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.6s' }}>
                    <div className="p-5 border-b border-white/[0.04]">
                        <h3 className="section-title">
                            <span className="section-dot bg-amber-500 text-amber-500" /> Aktivitas Terbaru
                        </h3>
                    </div>
                    <div className="p-5">
                        {recentActivity.length > 0 ? (
                            <div className="space-y-3">
                                {recentActivity.map((log, idx) => (
                                    <div
                                        key={log.id}
                                        className="flex items-center gap-3 text-sm animate-slide-in"
                                        style={{ animationDelay: `${idx * 0.05}s` }}
                                    >
                                        <Badge variant={statusVariant(log.action)}>
                                            {statusLabels[log.action] || log.action.replace('_', ' ')}
                                        </Badge>
                                        <span className="text-slate-400 truncate">{log.user?.name ?? 'Sistem'}</span>
                                        <span className="ml-auto text-slate-700 text-[10px] font-medium whitespace-nowrap">{log.created_at}</span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center text-slate-600 text-sm py-4">Belum ada aktivitas</div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
