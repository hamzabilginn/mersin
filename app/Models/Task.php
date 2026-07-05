<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'zzz_code',
        'tow',
        'stow',
        'sstow',
        'planned_qty',
        'planned_man_day',
        'fact_qty',
        'fact_man_day',
        'overtime',
        'comment',
        'local_id',
        'status',
        'tech_office_id',
        'hom_id',
        'sc_id',
        'pm_id',
        'due_date',
    ];

    public function techOffice(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tech_office_id');
    }

    public function hom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hom_id');
    }

    public function sc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sc_id');
    }

    public function pm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pm_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class)->orderBy('created_at', 'desc');
    }
}
