<?php

declare(strict_types=1);

namespace App\Domains\Worker\Models;

use App\Domains\Worker\Enums\WorkerComplaintStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerComplaintHistory extends Model
{
    protected $fillable = [
        'worker_complaint_id',
        'from_status',
        'to_status',
        'action',
        'comment',
        'performed_by',
    ];

    protected $casts = [
        'from_status' => WorkerComplaintStatus::class,
        'to_status'   => WorkerComplaintStatus::class,
    ];

    public function workerComplaint(): BelongsTo
    {
        return $this->belongsTo(WorkerComplaint::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
