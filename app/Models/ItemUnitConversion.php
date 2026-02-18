<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemUnitConversion extends Model
{
    protected $fillable = ['item_id', 'unit_name', 'conversion_qty'];

    protected $casts = [
        'conversion_qty' => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
