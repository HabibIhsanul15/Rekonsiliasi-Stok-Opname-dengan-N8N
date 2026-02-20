<?php

namespace App\Http\Controllers;

use App\Models\VarianceReview;

use App\Services\VarianceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VarianceReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\OpnameEntry::with(['item', 'session'])
            ->where('variance', '!=', 0);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('item', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        if ($request->has('date')) {
             $query->whereDate('created_at', $request->date);
        }

        $variances = $query->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Variances/Index', [
            'variances' => $variances,
            'filters' => $request->only(['search', 'date']),
        ]);
    }
}
