<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Models;

use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintHistory extends Model
{
    protected $fillable = [
        'complaint_id',
        'from_status',
        'to_status',
        'action',
        'comment',
        'performed_by',
    ];

    protected $casts = [
        'from_status' => ComplaintStatus::class,
        'to_status'   => ComplaintStatus::class,
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
