<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class OpnameSession extends Model
{
    protected $fillable = [
        'session_code', 'warehouse_id', 'conducted_by',
        'status', 'started_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(OpnameEntry::class);
    }

    public function imports(): HasMany
    {
        return $this->hasMany(OpnameImport::class);
    }

    public function varianceReviews(): HasManyThrough
    {
        return $this->hasManyThrough(VarianceReview::class, OpnameEntry::class);
    }

    // Auto-generate session code
    public static function generateCode(): string
    {
        $date = now()->format('Ymd');
        $last = static::where('session_code', 'like', "SO-{$date}-%")
            ->orderByDesc('session_code')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->session_code, -3);
            $next = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $next = '001';
        }

        return "SO-{$date}-{$next}";
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Stats
    public function getTotalEntriesAttribute(): int
    {
        return $this->entries()->count();
    }

    public function getVarianceCountAttribute(): int
    {
        return $this->entries()->where('variance', '!=', 0)->count();
    }
}
