import { Link } from '@inertiajs/react';
import { PaginationLink } from '@/types';

interface PaginationProps {
    links: PaginationLink[];
}

export default function Pagination({ links }: PaginationProps) {
    if (links.length <= 3) return null;

    return (
        <nav className="flex items-center justify-center gap-1">
            {links.map((link, idx) => (
                <Link
                    key={idx}
                    href={link.url || '#'}
                    className={`
                        px-3 py-1.5 text-[10px] font-bold rounded-lg transition-all duration-300
                        ${link.active
                            ? 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/20 shadow-lg shadow-indigo-500/5'
                            : link.url
                                ? 'text-slate-500 hover:text-slate-200 hover:bg-white/[0.03] border border-transparent'
                                : 'text-slate-700 cursor-default border border-transparent'
                        }
                    `}
                    preserveScroll
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </nav>
    );
}
