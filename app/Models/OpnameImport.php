<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpnameImport extends Model
{
    protected $fillable = [
        'opname_session_id', 'file_name', 'file_path',
        'total_rows', 'imported_rows', 'failed_rows',
        'status', 'errors', 'uploaded_by',
    ];

    protected $casts = [
        'errors' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(OpnameSession::class, 'opname_session_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
