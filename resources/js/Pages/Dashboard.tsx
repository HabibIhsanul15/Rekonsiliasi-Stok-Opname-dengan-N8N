import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import StatCard from '@/Components/StatCard';
import Badge, { statusLabels, statusVariant } from '@/Components/Badge';
import { OpnameEntry, ActivityLog } from '@/types';
import {
    PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend,
    AreaChart, Area, XAxis, YAxis, CartesianGrid
} from 'recharts';

interface ChartData {
    name: string;
    value: number;
    color?: string;
}

interface TrendData {
    date: string;
    value: number;
}

interface DashboardProps {
    totalEntries: number;
    totalVariances: number;
    accuracyRate: number;
    surplusItems: number;
    deficitItems: number;
    distributionData: ChartData[];
    trendData: TrendData[];
    topDiscrepancies: OpnameEntry[];
    recentActivity: ActivityLog[];
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
    totalEntries, totalVariances, accuracyRate,
    surplusItems, deficitItems,
    distributionData, trendData,
    topDiscrepancies, recentActivity
}: DashboardProps) {

    return (
        <AppLayout header={
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-xl font-bold text-white tracking-tight">Dasboard Eksekutif</h2>
                    <p className="text-xs text-slate-600 mt-0.5">Ringkasan performa akurasi stok</p>
                </div>
                <div className="text-[10px] text-slate-600 font-medium tracking-wide">
                    {new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                </div>
            </div>
        }>
            <Head title="Dasboard" />

            <div className="max-w-7xl mx-auto space-y-6">
                {/* ═══ 1. KPI Cards (Executive Summary) ═══ */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Hero Card: Accuracy Rate - The most important metric */}
                    <div className="glass-card p-5 relative overflow-hidden group">
                        <div className="absolute right-0 top-0 w-24 h-24 bg-emerald-500/10 rounded-bl-full -mr-4 -mt-4 transition-all group-hover:bg-emerald-500/20" />
                        <h3 className="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">Akurasi Stok</h3>
                        <div className="text-3xl font-bold text-white tracking-tight">{accuracyRate}%</div>
                        <p className="text-[10px] text-slate-500 mt-2">Target: &gt;98%</p>
                    </div>

                    <StatCard label="Total Item Diopname" value={totalEntries.toLocaleString('id-ID')} color="text-white" accent="#6366f1" />
                    <StatCard label="Total Item Selisih" value={totalVariances.toLocaleString('id-ID')} color="text-orange-400" accent="#F97316" sub="Total barang bermasalah" />

                    {/* Combined Surplus/Deficit Card */}
                    <div className="glass-card p-5 flex flex-col justify-center">
                        <div className="flex justify-between items-center mb-1">
                            <span className="text-xs text-emerald-400 font-medium">Surplus (+)</span>
                            <span className="text-lg font-bold text-white">{surplusItems}</span>
                        </div>
                        <div className="w-full bg-slate-800 h-1.5 rounded-full mb-3 overflow-hidden">
                            <div className="h-full bg-emerald-500" style={{ width: `${(surplusItems / (surplusItems + deficitItems || 1)) * 100}%` }} />
                        </div>
                        <div className="flex justify-between items-center mb-1">
                            <span className="text-xs text-red-400 font-medium">Defisit (-)</span>
                            <span className="text-lg font-bold text-white">{deficitItems}</span>
                        </div>
                        <div className="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                            <div className="h-full bg-red-500" style={{ width: `${(deficitItems / (surplusItems + deficitItems || 1)) * 100}%` }} />
                        </div>
                    </div>
                </div>

                {/* ═══ 2. Charts (Deep Dive) ═══ */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {/* A. Distribusi Selisih (Pie Chart) */}
                    <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.2s' }}>
                        <h3 className="section-title mb-5">
                            <span className="section-dot bg-indigo-500 text-indigo-500" /> Distribusi Selisih
                        </h3>
                        {distributionData.reduce((a, b) => a + b.value, 0) > 0 ? (
                            <ResponsiveContainer width="100%" height={240}>
                                <PieChart>
                                    <Pie
                                        data={distributionData}
                                        cx="50%" cy="50%"
                                        innerRadius={70}
                                        outerRadius={95}
                                        paddingAngle={5}
                                        dataKey="value"
                                    >
                                        {distributionData.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={entry.color} stroke="none" />
                                        ))}
                                    </Pie>
                                    <Tooltip contentStyle={tooltipStyle} />
                                    <Legend wrapperStyle={{ fontSize: '12px', fontWeight: 600, color: '#94a3b8' }} iconSize={10} verticalAlign="bottom" height={36} />
                                </PieChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="h-[240px] flex items-center justify-center text-slate-600 text-sm">Belum ada data selisih</div>
                        )}
                    </div>

                    {/* B. Trend Selisih (Area Chart) */}
                    <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.3s' }}>
                        <h3 className="section-title mb-5 flex justify-between items-center">
                            <span><span className="section-dot bg-blue-500 text-blue-500" /> Trend Selisih Harian</span>
                            <span className="text-[10px] text-slate-500 font-mono">7 HARI TERAKHIR</span>
                        </h3>
                        {trendData.length > 0 ? (
                            <ResponsiveContainer width="100%" height={240}>
                                <AreaChart data={trendData} margin={{ top: 10, right: 0, left: 0, bottom: 0 }}>
                                    <defs>
                                        <linearGradient id="colorDiff" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor="#3B82F6" stopOpacity={0.3} />
                                            <stop offset="95%" stopColor="#3B82F6" stopOpacity={0} />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#ffffff0a" />
                                    <XAxis dataKey="date" tick={{ fontSize: 10, fill: '#64748b' }} axisLine={false} tickLine={false} />
                                    <YAxis tick={{ fontSize: 10, fill: '#64748b' }} axisLine={false} tickLine={false} />
                                    <Tooltip contentStyle={tooltipStyle} cursor={{ stroke: '#ffffff10' }} />
                                    <Area type="monotone" dataKey="value" stroke="#3B82F6" strokeWidth={2} fillOpacity={1} fill="url(#colorDiff)" name="Total Selisih (Qty Absolut)" />
                                </AreaChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="h-[240px] flex items-center justify-center text-slate-600 text-sm">Belum ada data trend</div>
                        )}
                    </div>
                </div>

                {/* ═══ 3. Top Discrepancies Table ═══ */}
                <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.5s' }}>
                    <div className="p-5 border-b border-white/[0.04] flex justify-between items-center">
                        <h3 className="section-title">
                            <span className="section-dot bg-amber-500 text-amber-500" /> 10 Item Selisih Terbesar
                        </h3>
                        <span className="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Priority to Fix</span>
                    </div>
                    {topDiscrepancies.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th className="text-center">Sistem</th>
                                        <th className="text-center">Fisik</th>
                                        <th className="text-center">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {topDiscrepancies.map(entry => (
                                        <tr key={entry.id}>
                                            <td>
                                                <span className="font-semibold text-slate-200">{entry.item?.name ?? '-'}</span>
                                                <span className="block text-[10px] text-slate-600 mt-0.5">{entry.item?.item_code ?? '-'}</span>
                                            </td>
                                            <td className="text-center text-slate-400 tabular-nums">{Number(entry.system_qty).toLocaleString()}</td>
                                            <td className="text-center text-slate-300 font-medium tabular-nums">{Number(entry.counted_qty).toLocaleString()}</td>
                                            <td className={`text-center font-bold tabular-nums ${Number(entry.variance) < 0 ? 'text-red-400' : Number(entry.variance) > 0 ? 'text-emerald-400' : 'text-slate-600'}`}>
                                                {Number(entry.variance) > 0 ? '+' : ''}{Number(entry.variance).toLocaleString()}
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

                {/* ═══ 4. Recent Activity (Minimized) ═══ */}
                {recentActivity.length > 0 && (
                    <div className="glass-card p-4 animate-fade-in-up" style={{ animationDelay: '0.6s' }}>
                        <h3 className="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Log Aktivitas Terakhir</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            {recentActivity.map((log) => (
                                <div key={log.id} className="flex items-center gap-2 text-xs text-slate-400 bg-slate-900/50 p-2 rounded border border-white/5">
                                    <div className={`w-1.5 h-1.5 rounded-full ${statusVariant(log.action) === 'success' ? 'bg-emerald-500' : 'bg-blue-500'}`} />
                                    <span className="truncate flex-1">{log.action.replace(/_/g, ' ')} oleh {log.user?.name}</span>
                                    <span className="text-[10px] text-slate-600">{log.created_at}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
