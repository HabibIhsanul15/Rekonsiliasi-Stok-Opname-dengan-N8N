<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OpnameEntry extends Model
{
    protected $fillable = [
        'opname_session_id', 'item_id', 'system_qty',
        'counted_qty', 'variance', 'variance_pct', 'notes',
    ];

    protected $casts = [
        'system_qty' => 'decimal:2',
        'counted_qty' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_pct' => 'decimal:2',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(OpnameSession::class, 'opname_session_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function varianceReview(): HasOne
    {
        return $this->hasOne(VarianceReview::class);
    }

    // Calculate variance automatically
    public function calculateVariance(): void
    {
        $this->variance = $this->counted_qty - $this->system_qty;
        $this->variance_pct = $this->system_qty != 0
            ? round(($this->variance / $this->system_qty) * 100, 2)
            : ($this->counted_qty != 0 ? 100 : 0);
    }
}
