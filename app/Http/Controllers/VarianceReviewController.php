<?php

namespace App\Http\Controllers;

use App\Models\VarianceReview;
use App\Models\Warehouse;
use App\Services\VarianceService;
use Illuminate\Http\Request;

class VarianceReviewController extends Controller
{
    public function __construct(private VarianceService $varianceService) {}

    public function index(Request $request)
    {
        $reviews = VarianceReview::with([
            'opnameEntry.item',
            'opnameEntry.session.warehouse',
            'reviewer',
        ])
            ->when($request->severity, fn($q, $s) => $q->where('severity', $s))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->warehouse_id, function ($q, $w) {
                $q->whereHas('opnameEntry.session', fn($sq) => $sq->where('warehouse_id', $w));
            })
            ->latest()
            ->paginate(20);

        $warehouses = Warehouse::active()->get();

        return view('variances.index', compact('reviews', 'warehouses'));
    }

    public function approve(Request $request, VarianceReview $review)
    {
        if (!$review->canBeReviewed()) {
            return back()->with('error', 'Review ini tidak bisa disetujui.');
        }

        $request->validate(['notes' => 'nullable|string|max:1000']);

        $this->varianceService->approve($review, auth()->id(), $request->notes);

        return back()->with('success', 'Variance berhasil disetujui.');
    }

    public function reject(Request $request, VarianceReview $review)
    {
        if (!$review->canBeReviewed()) {
            return back()->with('error', 'Review ini tidak bisa ditolak.');
        }

        $request->validate(['notes' => 'nullable|string|max:1000']);

        $this->varianceService->reject($review, auth()->id(), $request->notes);

        return back()->with('success', 'Variance berhasil ditolak.');
    }
}
