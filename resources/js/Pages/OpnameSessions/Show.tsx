import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Badge, { statusVariant, statusLabels, severityVariant } from '@/Components/Badge';

interface Item {
    id: number;
    item_code: string;
    name: string;
    unit: string;
}

interface VarianceReview {
    id: number;
    severity: string;
    status: string;
    auto_resolved: boolean;
    reviewed_at: string | null;
    resolution_notes: string | null;
}

interface OpnameEntry {
    id: number;
    item_id: number;
    system_qty: number;
    counted_qty: number;
    variance: number;
    variance_pct: number;
    notes: string | null;
    item: Item;
    variance_review: VarianceReview | null;
}

interface OpnameSession {
    id: number;
    session_code: string;
    opname_date: string;
    status: string;
    started_at: string;
    completed_at: string | null;
    notes: string | null;
    conductor: { id: number; name: string } | null;
    entries: OpnameEntry[];
    variance_reviews: VarianceReview[];
}

interface ShowProps {
    session: OpnameSession;
}

export default function Show({ session }: ShowProps) {
    const totalEntries = session.entries?.length || 0;
    const varianceEntries = session.entries?.filter(e => e.variance !== 0) || [];
    const totalVariance = varianceEntries.reduce((sum, e) => sum + Math.abs(e.variance), 0);

    const handleComplete = () => {
        if (confirm('Apakah Anda yakin ingin menyelesaikan sesi opname ini? Status akan berubah menjadi "Selesai".')) {
            router.post(`/opname-sessions/${session.id}/complete`);
        }
    };

    const handleDelete = () => {
        if (confirm('Apakah Anda yakin ingin menghapus sesi opname ini? Semua data terkait akan dihapus.')) {
            router.delete(`/opname-sessions/${session.id}`);
        }
    };

    return (
        <AppLayout
            header={<h2 className="font-semibold text-xl text-slate-200 leading-tight">Detail Opname</h2>}
        >
            <Head title={`Opname ${session.session_code}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Session Info */}
                    <div className="glass-card p-8 animate-fade-in-up">
                        <div className="flex justify-between items-start mb-6">
                            <div>
                                <h2 className="text-2xl font-bold text-white">{session.session_code}</h2>
                                <p className="text-sm text-slate-400 mt-1">
                                    {session.opname_date ? new Date(session.opname_date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : '-'}
                                </p>
                            </div>
                            <Badge variant={statusVariant(session.status)}>
                                {statusLabels[session.status] || session.status}
                            </Badge>
                        </div>

                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div className="bg-white/[0.03] rounded-xl p-4 border border-white/[0.06]">
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Item</p>
                                <p className="text-2xl font-bold text-white mt-1">{totalEntries}</p>
                            </div>
                            <div className="bg-white/[0.03] rounded-xl p-4 border border-white/[0.06]">
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Varian Ditemukan</p>
                                <p className="text-2xl font-bold text-amber-400 mt-1">{varianceEntries.length}</p>
                            </div>
                            <div className="bg-white/[0.03] rounded-xl p-4 border border-white/[0.06]">
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Selisih</p>
                                <p className="text-2xl font-bold text-red-400 mt-1">{totalVariance.toFixed(0)}</p>
                            </div>
                            <div className="bg-white/[0.03] rounded-xl p-4 border border-white/[0.06]">
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Petugas</p>
                                <p className="text-lg font-semibold text-slate-200 mt-1">{session.conductor?.name || '-'}</p>
                            </div>
                        </div>

                        {session.notes && (
                            <div className="mt-4 p-3 rounded-lg bg-white/[0.02] border border-white/[0.04]">
                                <p className="text-xs text-slate-500 font-medium mb-1">Catatan</p>
                                <p className="text-sm text-slate-300">{session.notes}</p>
                            </div>
                        )}

                        {/* Action Buttons */}
                        <div className="mt-6 flex flex-wrap gap-3 border-t border-white/[0.06] pt-6">
                            {session.status === 'in_progress' && (
                                <button
                                    onClick={handleComplete}
                                    className="btn-primary"
                                >
                                    ✓ Selesaikan Rekonsiliasi
                                </button>
                            )}

                            {session.status === 'completed' && (
                                <div className="flex items-center gap-2 text-sm text-emerald-400">
                                    <span>✓</span>
                                    <span>Rekonsiliasi selesai pada {session.completed_at ? new Date(session.completed_at).toLocaleDateString('id-ID') : '-'}</span>
                                </div>
                            )}

                            <button
                                onClick={handleDelete}
                                className="px-4 py-2 rounded-xl text-sm font-medium text-red-400 hover:text-red-300 border border-red-500/20 hover:border-red-500/40 hover:bg-red-500/10 transition-all"
                            >
                                Hapus Sesi
                            </button>
                        </div>
                    </div>

                    {/* Entries Table */}
                    <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.1s' }}>
                        <div className="p-6 border-b border-white/[0.04] flex justify-between items-center">
                            <h3 className="text-lg font-medium text-white">Daftar Entri Opname</h3>
                            <Link
                                href="/import"
                                className="text-sm text-indigo-400 hover:text-indigo-300 transition-colors"
                            >
                                ← Kembali
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th className="text-right">Stok Sistem</th>
                                        <th className="text-right">Stok Fisik</th>
                                        <th className="text-right">Selisih</th>
                                        <th>Severity</th>
                                        <th>Status Review</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {session.entries?.map((entry, index) => (
                                        <tr key={entry.id}>
                                            <td className="text-slate-500 text-xs">{index + 1}</td>
                                            <td className="font-medium text-slate-200">{entry.item?.item_code}</td>
                                            <td className="text-slate-300">{entry.item?.name}</td>
                                            <td className="text-right text-slate-400">{entry.system_qty}</td>
                                            <td className="text-right text-slate-200 font-medium">{entry.counted_qty}</td>
                                            <td className={`text-right font-bold ${entry.variance === 0 ? 'text-emerald-400' : entry.variance > 0 ? 'text-blue-400' : 'text-red-400'}`}>
                                                {entry.variance > 0 && '+'}{entry.variance}
                                            </td>
                                            <td>
                                                {entry.variance_review ? (
                                                    <Badge variant={severityVariant(entry.variance_review.severity)}>
                                                        {entry.variance_review.severity}
                                                    </Badge>
                                                ) : (
                                                    <span className="text-slate-600 text-xs">-</span>
                                                )}
                                            </td>
                                            <td>
                                                {entry.variance_review ? (
                                                    <Badge variant={statusVariant(entry.variance_review.status)}>
                                                        {statusLabels[entry.variance_review.status] || entry.variance_review.status}
                                                    </Badge>
                                                ) : (
                                                    <span className="text-slate-600 text-xs">-</span>
                                                )}
                                            </td>
                                            <td className="text-slate-500 text-xs max-w-[150px] truncate">
                                                {entry.notes || '-'}
                                            </td>
                                        </tr>
                                    ))}
                                    {(!session.entries || session.entries.length === 0) && (
                                        <tr>
                                            <td colSpan={9} className="text-center py-8 text-slate-500">
                                                Belum ada entri opname.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
