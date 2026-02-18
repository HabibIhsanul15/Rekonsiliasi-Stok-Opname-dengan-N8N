import { Head, useForm, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Pagination from '@/Components/Pagination';
import Badge, { statusVariant, statusLabels } from '@/Components/Badge';
import { OpnameImport, PaginatedData } from '@/types';

interface ImportIndexProps {
    imports: PaginatedData<OpnameImport>;
}

export default function ImportIndex({ imports }: ImportIndexProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        file: null as File | null,
        opname_date: new Date().toISOString().split('T')[0],
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/import/upload', {
            onSuccess: () => reset('file'),
            forceFormData: true,
        });
    };

    const handleDelete = (sessionId: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus riwayat opname ini? Data yang terkait akan hilang.')) {
            router.delete(`/opname-sessions/${sessionId}`);
        }
    };

    return (
        <AppLayout
            header={<h2 className="font-semibold text-xl text-slate-200 leading-tight">Import Stock Opname</h2>}
        >
            <Head title="Import" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Upload Section */}
                    <div className="glass-card p-8 animate-fade-in-up">
                        <header>
                            <h2 className="text-lg font-medium text-white mb-1">Upload File Stock Opname</h2>
                            <p className="text-sm text-slate-400">
                                Unggah file CSV atau Excel (.xlsx) hasil perhitungan fisik.
                            </p>
                        </header>

                        <form onSubmit={submit} className="mt-6 space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label htmlFor="opname_date" className="form-label">Tanggal Opname</label>
                                    <input
                                        id="opname_date"
                                        type="date"
                                        className="form-input-dark"
                                        value={data.opname_date}
                                        onChange={(e) => setData('opname_date', e.target.value)}
                                        required
                                    />
                                    {errors.opname_date && <p className="mt-2 text-sm text-red-400">{errors.opname_date}</p>}
                                </div>

                                <div>
                                    <label htmlFor="file" className="form-label">File CSV/Excel</label>
                                    <input
                                        id="file"
                                        type="file"
                                        accept=".csv,.xlsx,.xls"
                                        className="form-input-dark file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/10 file:text-indigo-400 hover:file:bg-indigo-500/20"
                                        onChange={(e) => setData('file', e.target.files ? e.target.files[0] : null)}
                                    />
                                    {errors.file && <p className="mt-2 text-sm text-red-400">{errors.file}</p>}
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Upload
                                </button>

                                {processing && (
                                    <p className="text-sm text-slate-400 animate-pulse">Uploading...</p>
                                )}
                            </div>
                        </form>

                        <div className="border-t border-white/[0.06] mt-8 pt-6">
                            <div className="flex justify-between items-center mb-3">
                                <h4 className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Contoh Format CSV:</h4>
                                <a
                                    href="/template_opname.csv"
                                    download
                                    className="text-[10px] text-indigo-400 hover:text-indigo-300 underline font-medium transition-colors"
                                >
                                    Download Template
                                </a>
                            </div>
                            <div className="rounded-xl p-4 font-mono text-[11px] text-slate-400 overflow-x-auto bg-[#050a18]/50 border border-white/[0.04]">
                                item_code,item_name,counted_qty,notes<br />
                                100001,Paper Bag,128,<br />
                                100002,Cup Paper,1100,<br />
                                100003,whipping cream,11000,Kemasan rusak 1
                            </div>
                        </div>
                    </div>

                    {/* Import History */}
                    <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.1s' }}>
                        <div className="p-6 border-b border-white/[0.04]">
                            <h2 className="text-lg font-medium text-white">Riwayat Import</h2>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>File</th>
                                        <th>Status</th>
                                        <th>Rows</th>
                                        <th>Petugas</th>
                                        <th className="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {imports.data.map((imp) => (
                                        <tr key={imp.id}>
                                            <td className="text-slate-400">
                                                {new Date(imp.created_at).toLocaleDateString()}
                                                <div className="text-[10px] text-slate-600 mt-0.5">
                                                    {new Date(imp.created_at).toLocaleTimeString()}
                                                </div>
                                            </td>
                                            <td className="font-medium text-slate-200">
                                                {imp.file_name}
                                            </td>
                                            <td>
                                                <Badge variant={statusVariant(imp.status)}>
                                                    {statusLabels[imp.status] || imp.status}
                                                </Badge>
                                            </td>
                                            <td className="text-slate-400">
                                                <span className="text-emerald-400 font-medium">{imp.imported_rows}</span>
                                                <span className="text-slate-600 mx-1">/</span>
                                                <span>{imp.total_rows}</span>
                                            </td>
                                            <td className="text-slate-400">
                                                {imp.uploader?.name || '-'}
                                            </td>
                                            <td className="text-right">
                                                {imp.session ? (
                                                    <button
                                                        onClick={() => handleDelete(imp.session!.id)}
                                                        className="text-red-400 hover:text-red-300 text-xs font-semibold transition-colors px-2 py-1 rounded hover:bg-red-500/10"
                                                    >
                                                        Hapus
                                                    </button>
                                                ) : (
                                                    <span className="text-slate-600 text-xs italic">Deleted</span>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                    {imports.data.length === 0 && (
                                        <tr>
                                            <td colSpan={6} className="text-center py-8 text-slate-500">
                                                Belum ada riwayat import.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                        {imports.links && (
                            <div className="p-4 border-t border-white/[0.04]">
                                <Pagination links={imports.links} />
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
