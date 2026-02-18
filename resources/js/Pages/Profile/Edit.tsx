import { Head, useForm, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function ProfileEdit() {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user!;

    // Profile Info Form
    const profileForm = useForm({
        name: user.name,
        email: user.email,
    });

    // Password Form
    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    // Delete Form
    const deleteForm = useForm({
        password: '',
    });

    const updateProfile = (e: React.FormEvent) => {
        e.preventDefault();
        profileForm.patch('/profile');
    };

    const updatePassword = (e: React.FormEvent) => {
        e.preventDefault();
        passwordForm.put('/password', {
            onSuccess: () => passwordForm.reset(),
        });
    };

    const deleteAccount = (e: React.FormEvent) => {
        e.preventDefault();
        if (confirm('Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan.')) {
            deleteForm.delete('/profile');
        }
    };

    return (
        <AppLayout header={
            <div>
                <h2 className="text-xl font-bold text-white tracking-tight">Profil Saya</h2>
                <p className="text-xs text-slate-600 mt-0.5">Kelola informasi akun dan keamanan</p>
            </div>
        }>
            <Head title="Profil" />

            <div className="max-w-3xl mx-auto space-y-6">
                {/* Profile Information */}
                <div className="glass-card p-6 animate-fade-in-up">
                    <h3 className="section-title mb-5">
                        <span className="section-dot bg-indigo-500 text-indigo-500" /> Informasi Profil
                    </h3>
                    <p className="text-xs text-slate-500 mb-5">Perbarui nama dan alamat email akun Anda.</p>

                    <form onSubmit={updateProfile} className="space-y-5">
                        <div>
                            <label htmlFor="name" className="form-label">Nama</label>
                            <input
                                id="name"
                                type="text"
                                value={profileForm.data.name}
                                onChange={e => profileForm.setData('name', e.target.value)}
                                className="form-input-dark"
                                required
                            />
                            {profileForm.errors.name && <p className="mt-1.5 text-xs text-red-400">{profileForm.errors.name}</p>}
                        </div>

                        <div>
                            <label htmlFor="email" className="form-label">Email</label>
                            <input
                                id="email"
                                type="email"
                                value={profileForm.data.email}
                                onChange={e => profileForm.setData('email', e.target.value)}
                                className="form-input-dark"
                                required
                            />
                            {profileForm.errors.email && <p className="mt-1.5 text-xs text-red-400">{profileForm.errors.email}</p>}
                        </div>

                        <button type="submit" className="btn-primary py-2.5 px-6 text-xs font-bold" disabled={profileForm.processing}>
                            {profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                        </button>
                    </form>
                </div>

                {/* Change Password */}
                <div className="glass-card p-6 animate-fade-in-up" style={{ animationDelay: '0.1s' }}>
                    <h3 className="section-title mb-5">
                        <span className="section-dot bg-amber-500 text-amber-500" /> Ubah Kata Sandi
                    </h3>
                    <p className="text-xs text-slate-500 mb-5">Pastikan akun Anda menggunakan kata sandi yang kuat.</p>

                    <form onSubmit={updatePassword} className="space-y-5">
                        <div>
                            <label htmlFor="current_password" className="form-label">Kata Sandi Saat Ini</label>
                            <input
                                id="current_password"
                                type="password"
                                value={passwordForm.data.current_password}
                                onChange={e => passwordForm.setData('current_password', e.target.value)}
                                className="form-input-dark"
                                placeholder="••••••••"
                                required
                            />
                            {passwordForm.errors.current_password && <p className="mt-1.5 text-xs text-red-400">{passwordForm.errors.current_password}</p>}
                        </div>

                        <div>
                            <label htmlFor="password" className="form-label">Kata Sandi Baru</label>
                            <input
                                id="password"
                                type="password"
                                value={passwordForm.data.password}
                                onChange={e => passwordForm.setData('password', e.target.value)}
                                className="form-input-dark"
                                placeholder="Minimal 8 karakter"
                                required
                            />
                            {passwordForm.errors.password && <p className="mt-1.5 text-xs text-red-400">{passwordForm.errors.password}</p>}
                        </div>

                        <div>
                            <label htmlFor="password_confirmation" className="form-label">Konfirmasi Kata Sandi Baru</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                value={passwordForm.data.password_confirmation}
                                onChange={e => passwordForm.setData('password_confirmation', e.target.value)}
                                className="form-input-dark"
                                placeholder="Ulangi kata sandi baru"
                                required
                            />
                            {passwordForm.errors.password_confirmation && <p className="mt-1.5 text-xs text-red-400">{passwordForm.errors.password_confirmation}</p>}
                        </div>

                        <button type="submit" className="btn-primary py-2.5 px-6 text-xs font-bold" disabled={passwordForm.processing}>
                            {passwordForm.processing ? 'Menyimpan...' : 'Ubah Kata Sandi'}
                        </button>
                    </form>
                </div>

                {/* Delete Account */}
                <div className="glass-card p-6 border-red-500/10 animate-fade-in-up" style={{ animationDelay: '0.2s' }}>
                    <h3 className="section-title mb-5">
                        <span className="section-dot bg-red-500 text-red-500" /> Hapus Akun
                    </h3>
                    <p className="text-xs text-slate-500 mb-5">
                        Setelah akun dihapus, semua data akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                    </p>

                    <form onSubmit={deleteAccount} className="space-y-5">
                        <div>
                            <label htmlFor="delete_password" className="form-label">Konfirmasi Kata Sandi</label>
                            <input
                                id="delete_password"
                                type="password"
                                value={deleteForm.data.password}
                                onChange={e => deleteForm.setData('password', e.target.value)}
                                className="form-input-dark"
                                placeholder="Masukkan kata sandi untuk konfirmasi"
                                required
                            />
                            {deleteForm.errors.password && <p className="mt-1.5 text-xs text-red-400">{deleteForm.errors.password}</p>}
                        </div>

                        <button type="submit" className="btn-danger py-2.5 px-6 text-xs font-bold" disabled={deleteForm.processing}>
                            {deleteForm.processing ? 'Menghapus...' : 'Hapus Akun Permanen'}
                        </button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
