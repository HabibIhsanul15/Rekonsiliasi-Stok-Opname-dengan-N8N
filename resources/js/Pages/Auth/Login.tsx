import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

interface LoginProps {
    status?: string;
    canResetPassword?: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Masuk" />

            <h2 className="text-lg font-bold text-white mb-1">Selamat Datang</h2>
            <p className="text-xs text-slate-500 mb-6">Masuk ke akun Anda untuk melanjutkan</p>

            {status && (
                <div className="mb-4 p-3 rounded-xl bg-emerald-500/5 border border-emerald-500/15 text-sm text-emerald-400">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
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
                        autoFocus
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
                        placeholder="••••••••"
                        autoComplete="current-password"
                        required
                    />
                    {errors.password && <p className="mt-1.5 text-xs text-red-400">{errors.password}</p>}
                </div>

                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={e => setData('remember', e.target.checked)}
                            className="w-4 h-4 rounded border-white/10 bg-white/5 text-indigo-500 focus:ring-indigo-500/30 focus:ring-offset-0"
                        />
                        <span className="text-xs text-slate-500">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" className="w-full btn-primary py-3 text-sm font-bold" disabled={processing}>
                    {processing ? 'Memproses...' : 'Masuk'}
                </button>
            </form>
        </GuestLayout>
    );
}
