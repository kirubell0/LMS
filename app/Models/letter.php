<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref_no',
        'date',
        'to',
        'Subject',
        'body',
        'cc',
        'approved_by',
        'approved_position',
        'pdf_path',
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
