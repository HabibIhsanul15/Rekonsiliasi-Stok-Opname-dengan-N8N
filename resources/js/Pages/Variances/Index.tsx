import { Head, useForm, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Badge, { severityVariant, severityLabels, statusVariant, statusLabels } from '@/Components/Badge';
import Pagination from '@/Components/Pagination';
import { VarianceReview, PaginatedData } from '@/types';
import { useState } from 'react';

interface VariancesProps {
    reviews: PaginatedData<VarianceReview>;
}

export default function VariancesIndex({ reviews }: VariancesProps) {
    const [filterForm, setFilterForm] = useState({ severity: '', status: '' });
    const [actionMenuId, setActionMenuId] = useState<number | null>(null);

    const applyFilter = (e: React.FormEvent) => {
        e.preventDefault();
        const params = new URLSearchParams();
        if (filterForm.severity) params.set('severity', filterForm.severity);
        if (filterForm.status) params.set('status', filterForm.status);
        router.get(`/variances?${params.toString()}`);
    };

    const handleAction = (reviewId: number, action: 'approve' | 'reject') => {
        router.post(`/variances/${reviewId}/${action}`);
        setActionMenuId(null);
    };

    return (
        <AppLayout header={
            <div>
                <h2 className="text-xl font-bold text-white tracking-tight">Tinjauan Selisih</h2>
                <p className="text-xs text-slate-600 mt-0.5">Tinjau dan kelola selisih stock opname</p>
            </div>
        }>
            <Head title="Tinjauan Selisih" />

            <div className="max-w-7xl mx-auto space-y-6">
                {/* Filter */}
                <div className="glass-card p-5 animate-fade-in-up">
                    <form onSubmit={applyFilter} className="flex flex-wrap items-end gap-4">
                        <div className="flex-1 min-w-[160px]">
                            <label className="form-label">Keparahan</label>
                            <select
                                value={filterForm.severity}
                                onChange={e => setFilterForm({ ...filterForm, severity: e.target.value })}
                                className="form-input-dark"
                            >
                                <option value="">Semua Keparahan</option>
                                <option value="low">Rendah</option>
                                <option value="medium">Sedang</option>
                                <option value="high">Tinggi</option>
                                <option value="critical">Kritis</option>
                            </select>
                        </div>
                        <div className="flex-1 min-w-[160px]">
                            <label className="form-label">Status</label>
                            <select
                                value={filterForm.status}
                                onChange={e => setFilterForm({ ...filterForm, status: e.target.value })}
                                className="form-input-dark"
                            >
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                                <option value="auto_approved">Otomatis</option>
                                <option value="escalated">Eskalasi</option>
                            </select>
                        </div>
                        <button type="submit" className="btn-primary py-2.5 px-6 text-xs font-bold">Filter</button>
                    </form>
                </div>

                {/* Table */}
                <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.1s' }}>
                    <div className="p-5 border-b border-white/[0.04]">
                        <h3 className="section-title">
                            <span className="section-dot bg-orange-500 text-orange-500" /> Daftar Selisih
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
                                    <th className="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {reviews.data.length > 0 ? (
                                    reviews.data.map(review => (
                                        <tr key={review.id}>
                                            <td>
                                                <span className="font-semibold text-slate-200">{review.opnameEntry?.item?.name ?? '-'}</span>
                                                <span className="block text-[10px] text-slate-600 mt-0.5">{review.opnameEntry?.item?.item_code ?? '-'}</span>
                                            </td>
                                            <td className="text-right text-slate-400 tabular-nums">{Number(review.opnameEntry?.system_qty ?? 0).toFixed(2)}</td>
                                            <td className="text-right text-slate-300 font-medium tabular-nums">{Number(review.opnameEntry?.counted_qty ?? 0).toFixed(2)}</td>
                                            <td className={`text-right font-bold tabular-nums ${Number(review.opnameEntry?.variance ?? 0) < 0 ? 'text-red-400' : Number(review.opnameEntry?.variance ?? 0) > 0 ? 'text-emerald-400' : 'text-slate-600'}`}>
                                                {Number(review.opnameEntry?.variance ?? 0) > 0 ? '+' : ''}{Number(review.opnameEntry?.variance ?? 0).toFixed(2)}
                                            </td>
                                            <td className="text-center">
                                                <Badge variant={severityVariant(review.severity)}>
                                                    {severityLabels[review.severity] || review.severity}
                                                </Badge>
                                            </td>
                                            <td className="text-center">
                                                <Badge variant={statusVariant(review.status)}>
                                                    {statusLabels[review.status] || review.status}
                                                </Badge>
                                            </td>
                                            <td className="text-center">
                                                {review.status === 'pending' ? (
                                                    <div className="relative inline-block">
                                                        <button
                                                            onClick={() => setActionMenuId(actionMenuId === review.id ? null : review.id)}
                                                            className="btn-secondary py-1 px-3 text-[10px]"
                                                        >
                                                            Tindakan ▾
                                                        </button>
                                                        {actionMenuId === review.id && (
                                                            <div className="absolute right-0 mt-1 w-32 glass-card border border-white/10 rounded-xl overflow-hidden z-20 shadow-2xl">
                                                                <button
                                                                    onClick={() => handleAction(review.id, 'approve')}
                                                                    className="block w-full text-left px-4 py-2.5 text-xs text-emerald-400 hover:bg-emerald-500/10 transition"
                                                                >
                                                                    ✓ Setujui
                                                                </button>
                                                                <button
                                                                    onClick={() => handleAction(review.id, 'reject')}
                                                                    className="block w-full text-left px-4 py-2.5 text-xs text-red-400 hover:bg-red-500/10 transition"
                                                                >
                                                                    ✕ Tolak
                                                                </button>
                                                            </div>
                                                        )}
                                                    </div>
                                                ) : (
                                                    <span className="text-slate-700 text-[10px]">—</span>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={7} className="text-center py-10 text-slate-600 text-sm">Belum ada data selisih</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {reviews.links && (
                        <div className="px-5 py-3 border-t border-white/[0.04]">
                            <Pagination links={reviews.links} />
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
