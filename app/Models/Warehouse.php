<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['code', 'name', 'location', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function opnameSessions(): HasMany
    {
        return $this->hasMany(OpnameSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
