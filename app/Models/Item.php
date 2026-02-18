<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = ['item_code', 'name', 'category', 'unit', 'system_stock', 'warehouse_id'];

    protected $casts = [
        'system_stock' => 'decimal:2',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function opnameEntries(): HasMany
    {
        return $this->hasMany(OpnameEntry::class);
    }
}
