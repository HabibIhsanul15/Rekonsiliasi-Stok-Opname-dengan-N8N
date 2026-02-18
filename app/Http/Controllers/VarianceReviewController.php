<?php

namespace App\Http\Controllers;

use App\Models\VarianceReview;

use App\Services\VarianceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VarianceReviewController extends Controller
{
    public function __construct(private VarianceService $varianceService) {}

    public function index(Request $request)
    {
        $reviews = VarianceReview::with([
            'opnameEntry.item',
            'reviewer',
        ])
            ->when($request->severity, fn($q, $s) => $q->where('severity', $s))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return Inertia::render('Variances/Index', [
            'reviews' => $reviews,
        ]);
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
