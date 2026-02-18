import { ReactNode, useState } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { PageProps } from '@/types';

interface AppLayoutProps {
    header?: ReactNode;
    children: ReactNode;
}

const navItems = [
    { href: '/dashboard', label: 'Dasbor', icon: '◈' },
    { href: '/items', label: 'Data Barang', icon: '▣' },
    { href: '/variances', label: 'Tinjauan Selisih', icon: '◇' },
    { href: '/import', label: 'Import Data', icon: '◆' },
    { href: '/analytics', label: 'Analitik', icon: '◉' },
];

export default function AppLayout({ header, children }: AppLayoutProps) {
    const { auth, flash } = usePage<PageProps>().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

    const handleLogout = () => {
        router.post('/logout');
    };

    return (
        <div className="min-h-screen flex bg-[#050a18]">
            {/* ═══ Sidebar ═══ */}
            <aside className={`
                fixed inset-y-0 left-0 z-50 w-[260px] flex flex-col
                border-r border-white/[0.04]
                bg-[#060b1a]/90 backdrop-blur-2xl
                transform transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                lg:translate-x-0 lg:static
                ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
            `}>
                {/* Brand */}
                <div className="p-6 border-b border-white/[0.04]">
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <span className="text-white text-sm font-black">SO</span>
                        </div>
                        <div>
                            <h1 className="text-base font-bold text-white tracking-tight">StockOpname</h1>
                            <p className="text-[9px] text-slate-600 font-semibold tracking-[0.2em] uppercase">Sistem Rekonsiliasi</p>
                        </div>
                    </div>
                </div>

                {/* Navigation */}
                <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
                    <p className="text-[9px] font-bold text-slate-600 uppercase tracking-[0.2em] px-4 mb-3">Menu Utama</p>
                    {navItems.map((item, idx) => {
                        const isActive = currentPath === item.href || currentPath.startsWith(item.href + '/');
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`
                                    group flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium
                                    transition-all duration-300 animate-slide-in
                                    ${isActive
                                        ? 'text-indigo-300 bg-gradient-to-r from-indigo-500/10 to-purple-500/5 shadow-[inset_3px_0_0_#6366f1]'
                                        : 'text-slate-500 hover:text-slate-200 hover:bg-white/[0.03]'
                                    }
                                `}
                                style={{ animationDelay: `${idx * 0.05}s` }}
                            >
                                <span className={`text-base transition-transform duration-300 group-hover:scale-110 ${isActive ? 'text-indigo-400' : ''}`}>
                                    {item.icon}
                                </span>
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>

                {/* User Panel */}
                {auth.user && (
                    <div className="p-4 border-t border-white/[0.04]">
                        <div className="flex items-center gap-3 mb-3">
                            <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-black shadow-lg shadow-indigo-500/15">
                                {auth.user.name.charAt(0).toUpperCase()}
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-semibold text-slate-200 truncate">{auth.user.name}</p>
                                <p className="text-[10px] text-slate-600 truncate">{auth.user.email}</p>
                            </div>
                        </div>
                        <div className="flex gap-1.5">
                            <Link href="/profile" className="flex-1 text-center text-[10px] font-medium text-slate-500 hover:text-indigo-300 transition-all py-2 rounded-lg hover:bg-indigo-500/5 border border-transparent hover:border-indigo-500/10">
                                Profil
                            </Link>
                            <button onClick={handleLogout} className="flex-1 text-center text-[10px] font-medium text-slate-500 hover:text-red-400 transition-all py-2 rounded-lg hover:bg-red-500/5 border border-transparent hover:border-red-500/10">
                                Keluar
                            </button>
                        </div>
                    </div>
                )}
            </aside>

            {/* Overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden transition-opacity"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* ═══ Main Content ═══ */}
            <div className="flex-1 flex flex-col min-h-screen min-w-0">
                {/* Top Bar */}
                <header className="sticky top-0 z-30 border-b border-white/[0.04] bg-[#050a18]/80 backdrop-blur-xl px-6 py-4 flex items-center gap-4">
                    <button
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                        className="lg:hidden text-slate-500 hover:text-white transition-colors p-1"
                    >
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div className="flex-1 min-w-0">
                        {header}
                    </div>
                </header>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="mx-6 mt-4 p-4 rounded-xl border border-emerald-500/15 bg-emerald-500/5 text-emerald-300 text-sm flex items-center gap-3 animate-fade-in-up">
                        <span className="w-2 h-2 rounded-full bg-emerald-400 shadow-lg shadow-emerald-400/50" />
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mx-6 mt-4 p-4 rounded-xl border border-red-500/15 bg-red-500/5 text-red-300 text-sm flex items-center gap-3 animate-fade-in-up">
                        <span className="w-2 h-2 rounded-full bg-red-400 shadow-lg shadow-red-400/50" />
                        {flash.error}
                    </div>
                )}

                {/* Page Content */}
                <main className="flex-1 p-6">
                    {children}
                </main>

                {/* Footer */}
                <footer className="px-6 py-3 border-t border-white/[0.03] text-center">
                    <p className="text-[10px] text-slate-700">StockOpname © {new Date().getFullYear()} — Sistem Rekonsiliasi Stok</p>
                </footer>
            </div>
        </div>
    );
}
