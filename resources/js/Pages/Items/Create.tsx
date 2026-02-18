import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        item_code: '',
        name: '',
        jenis_barang: '',
        kategori_barang: '',
        unit: 'PCS',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/items');
    };

    return (
        <AppLayout
            header={<h2 className="font-semibold text-xl text-slate-200 leading-tight">Tambah Barang</h2>}
        >
            <Head title="Tambah Barang" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div className="glass-card p-8 animate-fade-in-up">
                        <header className="mb-6">
                            <h2 className="text-lg font-medium text-white">Informasi Barang</h2>
                            <p className="text-sm text-slate-400 mt-1">
                                Tambahkan data barang baru ke dalam sistem.
                            </p>
                        </header>

                        <form onSubmit={submit} className="space-y-6">
                            <div>
                                <label htmlFor="item_code" className="form-label">Kode Barang</label>
                                <input
                                    id="item_code"
                                    type="text"
                                    className="form-input-dark"
                                    value={data.item_code}
                                    onChange={(e) => setData('item_code', e.target.value)}
                                    placeholder="Contoh: 10001"
                                    autoFocus
                                    required
                                />
                                {errors.item_code && <p className="mt-2 text-xs text-red-400">{errors.item_code}</p>}
                            </div>

                            <div>
                                <label htmlFor="name" className="form-label">Nama Barang</label>
                                <input
                                    id="name"
                                    type="text"
                                    className="form-input-dark"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Contoh: Kertas HVS A4"
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
                                        placeholder="Contoh: ATK"
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
                                        placeholder="Opsional"
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
                                    placeholder="PCS, BOX, KG"
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
                                    Simpan Barang
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
