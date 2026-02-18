<?php

namespace App\Http\Controllers;

use App\Models\OpnameSession;
use App\Models\Warehouse;
use App\Services\VarianceService;
use Illuminate\Http\Request;

class OpnameSessionController extends Controller
{
    public function __construct(private VarianceService $varianceService) {}

    public function show(OpnameSession $opnameSession)
    {
        $opnameSession->load([
            'warehouse',
            'conductor',
            'entries.item',
            'entries.varianceReview',
        ]);

        $warehouseItems = $opnameSession->warehouse->items()->orderBy('item_code')->get();

        return view('opname-sessions.show', compact('opnameSession', 'warehouseItems'));
    }
}
