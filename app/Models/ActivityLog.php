<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = ['loggable_type', 'loggable_id', 'action', 'user_id', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper to log an action
    public static function log(Model $model, string $action, ?int $userId = null, ?array $metadata = null): static
    {
        return static::create([
            'loggable_type' => get_class($model),
            'loggable_id' => $model->getKey(),
            'action' => $action,
            'user_id' => $userId ?? auth()->id(),
            'metadata' => $metadata,
        ]);
    }
}
