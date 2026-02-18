import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Pagination from '@/Components/Pagination';
import { Item, PaginatedData } from '@/types';

interface IndexProps {
    items: PaginatedData<Item>;
}

export default function Index({ items }: IndexProps) {
    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus barang ini?')) {
            router.delete(`/items/${id}`);
        }
    };

    return (
        <AppLayout
            header={<h2 className="font-semibold text-xl text-slate-200 leading-tight">Data Barang</h2>}
        >
            <Head title="Data Barang" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="glass-card overflow-hidden animate-fade-in-up">
                        <div className="p-6 border-b border-white/[0.04] flex justify-between items-center">
                            <h2 className="text-lg font-medium text-white">Daftar Barang</h2>
                            <Link
                                href="/items/create"
                                className="btn-primary"
                            >
                                Tambah Barang
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Satuan</th>
                                        <th>Jenis / Kategori</th>
                                        <th className="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {items.data.map((item) => (
                                        <tr key={item.id}>
                                            <td className="font-medium text-slate-200">
                                                {item.item_code}
                                            </td>
                                            <td className="text-slate-300">
                                                {item.name}
                                            </td>
                                            <td className="text-slate-400">
                                                {item.unit}
                                            </td>
                                            <td className="text-slate-400">
                                                <div className="flex flex-col">
                                                    <span className="text-xs text-slate-300">{item.jenis_barang || '-'}</span>
                                                    <span className="text-[10px] text-slate-500">{item.kategori_barang}</span>
                                                </div>
                                            </td>
                                            <td className="text-right">
                                                <Link
                                                    href={`/items/${item.id}/edit`}
                                                    className="text-indigo-400 hover:text-indigo-300 text-xs font-medium mr-3 transition-colors"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(item.id)}
                                                    className="text-red-400 hover:text-red-300 text-xs font-medium transition-colors"
                                                >
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                    {items.data.length === 0 && (
                                        <tr>
                                            <td colSpan={5} className="text-center py-8 text-slate-500">
                                                Belum ada data barang.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {items.links && (
                            <div className="p-4 border-t border-white/[0.04]">
                                <Pagination links={items.links} />
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
