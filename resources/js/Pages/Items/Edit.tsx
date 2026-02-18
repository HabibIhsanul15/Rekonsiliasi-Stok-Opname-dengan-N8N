import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Item } from '@/types';

interface EditProps {
    item: Item;
}

export default function Edit({ item }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        item_code: item.item_code,
        name: item.name,
        jenis_barang: item.jenis_barang || '',
        kategori_barang: item.kategori_barang || '',
        unit: item.unit,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/items/${item.id}`);
    };

    return (
        <AppLayout
            header={<h2 className="font-semibold text-xl text-slate-200 leading-tight">Edit Barang</h2>}
        >
            <Head title="Edit Barang" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div className="glass-card p-8 animate-fade-in-up">
                        <header className="mb-6">
                            <h2 className="text-lg font-medium text-white">Edit Informasi Barang</h2>
                        </header>

                        <form onSubmit={submit} className="space-y-6">
                            <div>
                                <label htmlFor="item_code" className="form-label">Kode Barang</label>
                                <input
                                    id="item_code"
                                    type="text"
                                    className="form-input-dark opacity-75 cursor-not-allowed"
                                    value={data.item_code}
                                    readOnly
                                    title="Kode barang tidak dapat diubah"
                                />
                                <p className="mt-1 text-[10px] text-slate-500">Kode unik barang tidak dapat diubah.</p>
                            </div>

                            <div>
                                <label htmlFor="name" className="form-label">Nama Barang</label>
                                <input
                                    id="name"
                                    type="text"
                                    className="form-input-dark"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                />
                                {errors.name && <p className="mt-2 text-xs text-red-400">{errors.name}</p>}
                            </div>

                            <div className="grid grid-cols-2 gap-6">
                                <div>
                                    <label htmlFor="jenis_barang" className="form-label">Jenis Barang</label>
                                    <input
                                        id="jenis_barang"
                                        type="text"
                                        className="form-input-dark"
                                        value={data.jenis_barang}
                                        onChange={(e) => setData('jenis_barang', e.target.value)}
                                        required
                                    />
                                    {errors.jenis_barang && <p className="mt-2 text-xs text-red-400">{errors.jenis_barang}</p>}
                                </div>
                                <div>
                                    <label htmlFor="kategori_barang" className="form-label">Kategori</label>
                                    <input
                                        id="kategori_barang"
                                        type="text"
                                        className="form-input-dark"
                                        value={data.kategori_barang}
                                        onChange={(e) => setData('kategori_barang', e.target.value)}
                                    />
                                    {errors.kategori_barang && <p className="mt-2 text-xs text-red-400">{errors.kategori_barang}</p>}
                                </div>
                            </div>

                            <div>
                                <label htmlFor="unit" className="form-label">Satuan</label>
                                <input
                                    id="unit"
                                    type="text"
                                    className="form-input-dark"
                                    value={data.unit}
                                    onChange={(e) => setData('unit', e.target.value)}
                                    required
                                />
                                {errors.unit && <p className="mt-2 text-xs text-red-400">{errors.unit}</p>}
                            </div>

                            <div className="flex items-center gap-4 pt-4">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="btn-primary w-full sm:w-auto"
                                >
                                    Simpan Perubahan
                                </button>
                                <Link
                                    href="/items"
                                    className="text-sm text-slate-400 hover:text-slate-200 transition-colors"
                                >
                                    Batal
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
