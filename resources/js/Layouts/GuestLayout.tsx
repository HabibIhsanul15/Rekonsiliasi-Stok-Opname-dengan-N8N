import { PropsWithChildren } from 'react';

export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen flex items-center justify-center px-4 py-12 relative">
            {/* Background effects */}
            <div className="fixed inset-0 z-0">
                <div className="absolute top-1/4 left-1/4 w-96 h-96 rounded-full bg-indigo-500/5 blur-3xl" />
                <div className="absolute bottom-1/4 right-1/4 w-80 h-80 rounded-full bg-purple-500/5 blur-3xl" />
                <div className="absolute top-1/2 left-1/2 w-64 h-64 rounded-full bg-cyan-500/3 blur-3xl -translate-x-1/2 -translate-y-1/2" />
            </div>

            <div className="relative z-10 w-full max-w-md">
                {/* Brand */}
                <div className="text-center mb-8">
                    <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-500 shadow-2xl shadow-indigo-500/25 mb-4">
                        <span className="text-white text-xl font-black">SO</span>
                    </div>
                    <h1 className="text-2xl font-bold text-white tracking-tight">StockOpname</h1>
                    <p className="text-[10px] text-slate-600 font-semibold tracking-[0.2em] uppercase mt-1">Sistem Rekonsiliasi Stok</p>
                </div>

                {/* Card */}
                <div className="glass-card p-8">
                    {children}
                </div>
            </div>
        </div>
    );
}
