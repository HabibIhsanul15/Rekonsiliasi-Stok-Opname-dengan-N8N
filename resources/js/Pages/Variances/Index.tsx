import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Pagination from '@/Components/Pagination';
import { PaginatedData, OpnameEntry } from '@/types';
import { useState } from 'react';

// Kita pakai tipe OpnameEntry langsung karena controllernya sudah return OpnameEntry
interface VariancesProps {
    variances: PaginatedData<OpnameEntry>;
    filters: {
        search?: string;
        date?: string;
    };
}

export default function VariancesIndex({ variances, filters }: VariancesProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [date, setDate] = useState(filters.date || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/variances', { search, date }, { preserveState: true });
    };

    return (
        <AppLayout header={
            <div>
                <h2 className="text-xl font-bold text-white tracking-tight">Daftar Selisih Stock Opname</h2>
                <p className="text-xs text-slate-600 mt-0.5">Semua item dengan selisih stok (Variance != 0)</p>
            </div>
        }>
            <Head title="Daftar Selisih" />

            <div className="max-w-7xl mx-auto space-y-6">
                {/* Filter */}
                <div className="glass-card p-5 animate-fade-in-up">
                    <form onSubmit={handleSearch} className="flex flex-wrap items-end gap-4">
                        <div className="flex-1 min-w-[200px]">
                            <label className="form-label">Cari Barang</label>
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Kode atau Nama Barang..."
                                className="form-input-dark w-full"
                            />
                        </div>
                        <div className="w-[180px]">
                            <label className="form-label">Tanggal Opname</label>
                            <input
                                type="date"
                                value={date}
                                onChange={(e) => setDate(e.target.value)}
                                className="form-input-dark w-full"
                            />
                        </div>
                        <button type="submit" className="btn-primary py-2.5 px-6 text-xs font-bold">Filter</button>
                    </form>
                </div>

                {/* Table */}
                <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.1s' }}>
                    <div className="p-5 border-b border-white/[0.04] flex justify-between items-center">
                        <h3 className="section-title">
                            <span className="section-dot bg-orange-500 text-orange-500" /> Hasil Selisih
                        </h3>
                        <span className="text-xs text-slate-500">Total: {variances.total} item</span>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th className="text-right">Sistem</th>
                                    <th className="text-right">Fisik</th>
                                    <th className="text-right">Selisih</th>
                                    <th className="text-right">%</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {variances.data.length > 0 ? (
                                    variances.data.map(entry => (
                                        <tr key={entry.id}>
                                            <td className="text-xs text-slate-500">
                                                {entry.opname_date_formatted || new Date(entry.created_at).toLocaleDateString('id-ID')}
                                                <div className="text-[10px] opacity-70">{entry.session?.session_code}</div>
                                            </td>
                                            <td className="font-mono text-xs text-slate-400">
                                                {entry.item?.item_code ?? '-'}
                                            </td>
                                            <td>
                                                <span className="font-semibold text-slate-200">{entry.item?.name ?? '-'}</span>
                                                <span className="ml-2 text-[10px] px-1.5 py-0.5 bg-slate-800 rounded text-slate-500">{entry.item?.unit}</span>
                                            </td>
                                            <td className="text-right text-slate-400 tabular-nums">
                                                {Number(entry.system_qty).toLocaleString()}
                                            </td>
                                            <td className="text-right text-slate-300 font-medium tabular-nums">
                                                {Number(entry.counted_qty).toLocaleString()}
                                            </td>
                                            <td className={`text-right font-bold tabular-nums ${Number(entry.variance) < 0 ? 'text-red-400' : 'text-emerald-400'}`}>
                                                {Number(entry.variance) > 0 ? '+' : ''}{Number(entry.variance).toLocaleString()}
                                            </td>
                                            <td className="text-right text-xs text-slate-500 tabular-nums">
                                                {entry.variance_pct}%
                                            </td>
                                            <td className="text-xs text-slate-500 italic max-w-[200px] truncate">
                                                {entry.notes || '-'}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={8} className="text-center py-10 text-slate-600 text-sm">
                                            Tidak ada selisih ditemukan (Semua stok cocok 🎉)
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {variances.links && (
                        <div className="px-5 py-3 border-t border-white/[0.04]">
                            <Pagination links={variances.links} />
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
