import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import StatCard from '@/Components/StatCard';
import Badge, { severityLabels, severityVariant, statusLabels, statusVariant } from '@/Components/Badge';
import { PieChart, Pie, Cell, ResponsiveContainer, Legend, Tooltip, BarChart, Bar, XAxis, YAxis, CartesianGrid, LineChart, Line } from 'recharts';

interface AnalyticsProps {
    severityData: Record<string, number>;
    topItems: Array<{ name: string; item_code: string; total_variance: number }>;
    avgTurnaround: number;
    shrinkageTrend: Array<{ month: string; total_abs_variance: number }>;
    statusSummary: Record<string, number>;
}

const SEVERITY_COLORS: Record<string, string> = {
    low: '#10B981', medium: '#F59E0B', high: '#EF4444', critical: '#8B5CF6',
};

const STATUS_COLORS: Record<string, string> = {
    auto_approved: '#10B981', pending: '#3B82F6', approved: '#22C55E', rejected: '#EF4444', escalated: '#F59E0B',
};

const tooltipStyle = {
    background: 'rgba(10, 15, 30, 0.95)',
    border: '1px solid rgba(255,255,255,0.08)',
    borderRadius: '12px',
    color: '#e2e8f0',
    fontSize: '12px',
    boxShadow: '0 8px 32px rgba(0,0,0,0.3)',
};

export default function Analytics({
    severityData, topItems, avgTurnaround, shrinkageTrend, statusSummary,
}: AnalyticsProps) {
    const severityChart = Object.entries(severityData).map(([key, value]) => ({
        name: severityLabels[key] || key,
        value,
        color: SEVERITY_COLORS[key] || '#64748B',
    }));

    const statusChart = Object.entries(statusSummary).map(([key, value]) => ({
        name: statusLabels[key] || key,
        value,
        color: STATUS_COLORS[key] || '#64748B',
    }));

    const trendData = shrinkageTrend.map(item => ({
        month: item.month,
        total: item.total_abs_variance,
    }));

    return (
        <AppLayout header={
            <div>
                <h2 className="text-xl font-bold text-white tracking-tight">Analitik</h2>
                <p className="text-xs text-slate-600 mt-0.5">Analisis mendalam data stock opname</p>
            </div>
        }>
            <Head title="Analitik" />

            <div className="max-w-7xl mx-auto space-y-6">
                {/* Summary */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="animate-fade-in-up">
                        <StatCard label="Total Selisih" value={Object.values(severityData).reduce((a, b) => a + b, 0).toLocaleString('id-ID')} color="text-orange-400" accent="#F97316" />
                    </div>
                    <div className="animate-fade-in-up" style={{ animationDelay: '0.08s' }}>
                        <StatCard label="Rata-rata Penyelesaian" value={`${avgTurnaround} jam`} color="text-cyan-400" accent="#06B6D4" />
                    </div>
                    <div className="animate-fade-in-up" style={{ animationDelay: '0.16s' }}>
                        <StatCard label="Item Bermasalah" value={topItems.length.toLocaleString('id-ID')} color="text-red-400" accent="#EF4444" />
                    </div>
                </div>

                {/* Charts Row */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Severity Pie */}
                    <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.2s' }}>
                        <h3 className="section-title mb-5">
                            <span className="section-dot bg-indigo-500 text-indigo-500" /> Distribusi Keparahan
                        </h3>
                        {severityChart.length > 0 ? (
                            <ResponsiveContainer width="100%" height={260}>
                                <PieChart>
                                    <Pie data={severityChart} cx="50%" cy="50%" innerRadius={65} outerRadius={95} dataKey="value" paddingAngle={4} strokeWidth={0}>
                                        {severityChart.map((entry, i) => <Cell key={i} fill={entry.color} />)}
                                    </Pie>
                                    <Tooltip contentStyle={tooltipStyle} />
                                    <Legend wrapperStyle={{ color: '#64748b', fontSize: '11px', fontWeight: 500 }} />
                                </PieChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="flex items-center justify-center h-[260px] text-slate-600 text-sm">Belum ada data</div>
                        )}
                    </div>

                    {/* Status Bar */}
                    <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.3s' }}>
                        <h3 className="section-title mb-5">
                            <span className="section-dot bg-purple-500 text-purple-500" /> Ringkasan Status
                        </h3>
                        {statusChart.length > 0 ? (
                            <ResponsiveContainer width="100%" height={260}>
                                <BarChart data={statusChart} layout="vertical" margin={{ left: 10 }}>
                                    <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.04)" />
                                    <XAxis type="number" tick={{ fill: '#64748b', fontSize: 11 }} axisLine={false} tickLine={false} />
                                    <YAxis type="category" dataKey="name" tick={{ fill: '#94a3b8', fontSize: 11 }} axisLine={false} tickLine={false} width={80} />
                                    <Tooltip contentStyle={tooltipStyle} />
                                    <Bar dataKey="value" radius={[0, 6, 6, 0]} barSize={18}>
                                        {statusChart.map((entry, i) => <Cell key={i} fill={entry.color} />)}
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="flex items-center justify-center h-[260px] text-slate-600 text-sm">Belum ada data</div>
                        )}
                    </div>
                </div>

                {/* Trend Line */}
                <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.4s' }}>
                    <h3 className="section-title mb-5">
                        <span className="section-dot bg-cyan-500 text-cyan-500" /> Tren Selisih Bulanan
                    </h3>
                    {trendData.length > 0 ? (
                        <ResponsiveContainer width="100%" height={280}>
                            <LineChart data={trendData}>
                                <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.04)" />
                                <XAxis dataKey="month" tick={{ fill: '#64748b', fontSize: 11 }} axisLine={false} tickLine={false} />
                                <YAxis tick={{ fill: '#64748b', fontSize: 11 }} axisLine={false} tickLine={false} />
                                <Tooltip contentStyle={tooltipStyle} />
                                <Line type="monotone" dataKey="total" stroke="#6366f1" strokeWidth={2.5} dot={{ fill: '#6366f1', r: 4 }} activeDot={{ r: 6, fill: '#818cf8' }} />
                            </LineChart>
                        </ResponsiveContainer>
                    ) : (
                        <div className="flex items-center justify-center h-[280px] text-slate-600 text-sm">Belum ada data tren</div>
                    )}
                </div>

                {/* Top Items Table */}
                <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.5s' }}>
                    <div className="p-5 border-b border-white/[0.04]">
                        <h3 className="section-title">
                            <span className="section-dot bg-red-500 text-red-500" /> Item dengan Selisih Terbanyak
                        </h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th className="text-right">Total Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                {topItems.length > 0 ? topItems.map((item, idx) => (
                                    <tr key={idx}>
                                        <td className="text-slate-600 font-bold">{idx + 1}</td>
                                        <td>
                                            <span className="font-semibold text-slate-200">{item.name}</span>
                                            <span className="block text-[10px] text-slate-600 mt-0.5">{item.item_code}</span>
                                        </td>
                                        <td className="text-right text-red-400 font-bold tabular-nums">{Number(item.total_variance).toFixed(2)}</td>
                                    </tr>
                                )) : (
                                    <tr>
                                        <td colSpan={3} className="text-center py-10 text-slate-600 text-sm">Belum ada data</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
