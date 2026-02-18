import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import StatCard from '@/Components/StatCard';
import Badge, { severityVariant, severityLabels, statusVariant, statusLabels } from '@/Components/Badge';
import { OpnameSession } from '@/types';

interface SessionShowProps {
    opnameSession: OpnameSession;
}

export default function SessionShow({ opnameSession }: SessionShowProps) {
    const entries = opnameSession.entries || [];
    const totalEntries = entries.length;
    const withVariance = entries.filter(e => Number(e.variance) !== 0).length;
    const reviewed = entries.filter(e => e.varianceReview && e.varianceReview.status !== 'pending').length;

    return (
        <AppLayout header={
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-xl font-bold text-white tracking-tight">Detail Import</h2>
                    <p className="text-xs text-slate-600 mt-0.5">Kode: {opnameSession.session_code}</p>
                </div>
                <Link href="/import" className="btn-secondary text-xs py-2 px-4">
                    ← Kembali
                </Link>
            </div>
        }>
            <Head title={`Detail ${opnameSession.session_code}`} />

            <div className="max-w-7xl mx-auto space-y-6">
                {/* Summary Stats */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {[
                        { label: 'Total Item', value: totalEntries, color: 'text-white', accent: '#6366f1' },
                        { label: 'Selisih Ditemukan', value: withVariance, color: 'text-orange-400', accent: '#F97316' },
                        { label: 'Sudah Ditinjau', value: reviewed, color: 'text-emerald-400', accent: '#10B981' },
                        { label: 'Status', value: statusLabels[opnameSession.status] || opnameSession.status, color: 'text-indigo-400', accent: '#6366f1' },
                    ].map((card, idx) => (
                        <div key={card.label} className="animate-fade-in-up" style={{ animationDelay: `${idx * 0.08}s` }}>
                            <StatCard {...card} value={String(card.value)} />
                        </div>
                    ))}
                </div>

                {/* Entries Table */}
                <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.3s' }}>
                    <div className="p-5 border-b border-white/[0.04]">
                        <h3 className="section-title">
                            <span className="section-dot bg-cyan-500 text-cyan-500" /> Daftar Item
                        </h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th className="text-right">Sistem</th>
                                    <th className="text-right">Fisik</th>
                                    <th className="text-right">Selisih</th>
                                    <th className="text-center">Keparahan</th>
                                    <th className="text-center">Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {entries.length > 0 ? entries.map(entry => (
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
                                        <td className="text-center">
                                            {entry.varianceReview ? (
                                                <Badge variant={severityVariant(entry.varianceReview.severity)}>
                                                    {severityLabels[entry.varianceReview.severity] || entry.varianceReview.severity}
                                                </Badge>
                                            ) : (
                                                <span className="text-slate-700 text-[10px]">—</span>
                                            )}
                                        </td>
                                        <td className="text-center">
                                            {entry.varianceReview ? (
                                                <Badge variant={statusVariant(entry.varianceReview.status)}>
                                                    {statusLabels[entry.varianceReview.status] || entry.varianceReview.status}
                                                </Badge>
                                            ) : (
                                                Number(entry.variance) === 0 ? (
                                                    <Badge variant="green">Sesuai</Badge>
                                                ) : (
                                                    <span className="text-slate-700 text-[10px]">—</span>
                                                )
                                            )}
                                        </td>
                                        <td className="text-slate-500 text-xs max-w-[200px] truncate">{entry.notes || '-'}</td>
                                    </tr>
                                )) : (
                                    <tr>
                                        <td colSpan={7} className="text-center py-10 text-slate-600 text-sm">Belum ada data</td>
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
