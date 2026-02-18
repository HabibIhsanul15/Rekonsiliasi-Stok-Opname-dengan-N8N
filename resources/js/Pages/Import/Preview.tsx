import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

interface PreviewData {
    headers: string[];
    rows: Record<string, string>[];
    total: number;
}

interface PreviewProps {
    preview: PreviewData;
    fileName: string;
    opnameDate: string;
}

export default function Preview({ preview, fileName, opnameDate }: PreviewProps) {
    const handleConfirm = () => {
        router.post('/import/process');
    };

    const handleCancel = () => {
        router.visit('/import');
    };

    return (
        <AppLayout
            header={<h2 className="font-semibold text-xl text-slate-200 leading-tight">Konfirmasi Import</h2>}
        >
            <Head title="Konfirmasi Import" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* File Info */}
                    <div className="glass-card p-8 animate-fade-in-up">
                        <h2 className="text-lg font-medium text-white mb-4">Detail File</h2>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="bg-white/[0.03] rounded-xl p-4 border border-white/[0.06]">
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nama File</p>
                                <p className="text-sm font-medium text-slate-200 mt-1 truncate">{fileName}</p>
                            </div>
                            <div className="bg-white/[0.03] rounded-xl p-4 border border-white/[0.06]">
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tanggal Opname</p>
                                <p className="text-sm font-medium text-slate-200 mt-1">
                                    {new Date(opnameDate).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                                </p>
                            </div>
                            <div className="bg-white/[0.03] rounded-xl p-4 border border-white/[0.06]">
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Baris</p>
                                <p className="text-2xl font-bold text-white mt-1">{preview.total}</p>
                            </div>
                        </div>
                    </div>

                    {/* Preview Table */}
                    <div className="glass-card overflow-hidden animate-fade-in-up" style={{ animationDelay: '0.1s' }}>
                        <div className="p-6 border-b border-white/[0.04]">
                            <h3 className="text-lg font-medium text-white">Preview Data</h3>
                            <p className="text-sm text-slate-400 mt-1">
                                Menampilkan {Math.min(preview.rows.length, 10)} dari {preview.total} baris data. Periksa apakah data sudah sesuai sebelum mengimpor.
                            </p>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        {preview.headers.map((header) => (
                                            <th key={header}>{header}</th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {preview.rows.map((row, index) => (
                                        <tr key={index}>
                                            <td className="text-slate-500 text-xs">{index + 1}</td>
                                            {preview.headers.map((header) => (
                                                <td key={header} className="text-slate-300">
                                                    {row[header] || '-'}
                                                </td>
                                            ))}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {preview.total > preview.rows.length && (
                            <div className="p-4 border-t border-white/[0.04] text-center">
                                <p className="text-xs text-slate-500">
                                    ... dan {preview.total - preview.rows.length} baris lainnya
                                </p>
                            </div>
                        )}
                    </div>

                    {/* Action Buttons */}
                    <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.2s' }}>
                        <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div className="text-sm text-slate-400">
                                ⚠️ Pastikan data sudah benar. Proses import akan mencocokkan <strong className="text-slate-200">item_code</strong> dengan data barang yang sudah ada di sistem.
                            </div>
                            <div className="flex gap-3 shrink-0">
                                <button
                                    onClick={handleCancel}
                                    className="px-6 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 border border-white/[0.08] hover:border-white/[0.15] transition-all"
                                >
                                    Batal
                                </button>
                                <button
                                    onClick={handleConfirm}
                                    className="btn-primary"
                                >
                                    ✓ Konfirmasi & Import
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
