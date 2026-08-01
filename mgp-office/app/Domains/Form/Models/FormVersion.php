<?php

declare(strict_types=1);

namespace App\Domains\Form\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormVersion extends Model
{
    protected $fillable = [
        'form_id',
        'version_number',
        'xlsform_path',
        'xform_xml_path',
        'deployed_at',
        'odk_asset_uid',
        'odk_state',
        'odk_hash',
        'odk_submissions_count',
        'created_by',
    ];

    protected $casts = [
        'deployed_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
