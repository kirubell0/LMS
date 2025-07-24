<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref_no',
        'date',
        'to',
        'subject',
        'body',
        'cc',
        'approved_by',
        'approved_position',
        'pdf_path',
        'qr_code_path',
        'is_completed',
        'list_id'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }
}
