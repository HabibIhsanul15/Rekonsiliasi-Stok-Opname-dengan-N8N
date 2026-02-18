import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/register', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Daftar" />

            <h2 className="text-lg font-bold text-white mb-1">Buat Akun</h2>
            <p className="text-xs text-slate-500 mb-6">Daftarkan akun baru untuk menggunakan sistem</p>

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <label htmlFor="name" className="form-label">Nama Lengkap</label>
                    <input
                        id="name"
                        type="text"
                        value={data.name}
                        onChange={e => setData('name', e.target.value)}
                        className="form-input-dark"
                        placeholder="Masukkan nama lengkap"
                        autoComplete="name"
                        autoFocus
                        required
                    />
                    {errors.name && <p className="mt-1.5 text-xs text-red-400">{errors.name}</p>}
                </div>

                <div>
                    <label htmlFor="email" className="form-label">Email</label>
                    <input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={e => setData('email', e.target.value)}
                        className="form-input-dark"
                        placeholder="nama@email.com"
                        autoComplete="username"
                        required
                    />
                    {errors.email && <p className="mt-1.5 text-xs text-red-400">{errors.email}</p>}
                </div>

                <div>
                    <label htmlFor="password" className="form-label">Kata Sandi</label>
                    <input
                        id="password"
                        type="password"
                        value={data.password}
                        onChange={e => setData('password', e.target.value)}
                        className="form-input-dark"
                        placeholder="Minimal 8 karakter"
                        autoComplete="new-password"
                        required
                    />
                    {errors.password && <p className="mt-1.5 text-xs text-red-400">{errors.password}</p>}
                </div>

                <div>
                    <label htmlFor="password_confirmation" className="form-label">Konfirmasi Kata Sandi</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={e => setData('password_confirmation', e.target.value)}
                        className="form-input-dark"
                        placeholder="Ulangi kata sandi"
                        autoComplete="new-password"
                        required
                    />
                    {errors.password_confirmation && <p className="mt-1.5 text-xs text-red-400">{errors.password_confirmation}</p>}
                </div>

                <button type="submit" className="w-full btn-primary py-3 text-sm font-bold" disabled={processing}>
                    {processing ? 'Memproses...' : 'Daftar'}
                </button>
            </form>
        </GuestLayout>
    );
}
