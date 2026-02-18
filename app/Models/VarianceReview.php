<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VarianceReview extends Model
{
    protected $fillable = [
        'opname_entry_id', 'severity', 'status', 'auto_resolved',
        'reviewed_by', 'reviewed_at', 'resolution_notes',
        'adjustment_pushed', 'pushed_at', 'push_response',
    ];

    protected $casts = [
        'auto_resolved' => 'boolean',
        'reviewed_at' => 'datetime',
        'adjustment_pushed' => 'boolean',
        'pushed_at' => 'datetime',
        'push_response' => 'array',
    ];

    public function opnameEntry(): BelongsTo
    {
        return $this->belongsTo(OpnameEntry::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeEscalated($query)
    {
        return $query->where('status', 'escalated');
    }

    public function scopeNeedsAttention($query)
    {
        return $query->whereIn('status', ['pending', 'escalated']);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isEscalated(): bool
    {
        return $this->status === 'escalated';
    }

    public function canBeReviewed(): bool
    {
        return in_array($this->status, ['pending', 'escalated']);
    }
}
