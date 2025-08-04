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
        'to',
        'subject',
        'body',
        'cc',// <-- Add this line
        'approved_by',
        'approved_position',
        'date',
        'list_id',
        'is_completed',
        'pdf_path',
        'qr_code_path',
        'cc_position',
        'approved_by_optional',
        'approved_position_optional',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }
}
