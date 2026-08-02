<?php

declare(strict_types=1);

namespace App\Domains\Statistic\Exports;

use App\Domains\Complaint\Models\Complaint;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export Excel des plaintes non sensibles pour une période (EF-05 / ER-04).
 * Les plaintes sensibles (VBG) sont exclues : elles ne sortent jamais en clair.
 */
class ComplaintsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Carbon $from,
        private readonly Carbon $to,
    ) {}

    public function collection(): Collection
    {
        return Complaint::with(['complaintType', 'site.locality.commune.department.region', 'registeredBy'])
            ->whereBetween('created_at', [$this->from, $this->to])
            ->whereHas('complaintType', fn ($q) => $q->where('is_sensitive', false))
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'N° Plainte', 'Type', 'Statut', 'Niveau', 'Canal', 'Site', 'Région',
            'Objet', 'Date enregistrement', 'Échéance SLA', 'SLA dépassé', 'Enregistré par',
        ];
    }

    /** @param Complaint $complaint */
    public function map($complaint): array
    {
        $breached = $complaint->sla_deadline
            && $complaint->sla_deadline->isPast()
            && ! in_array($complaint->status->value, ['resolved', 'closed', 'rejected'], true);

        return [
            $complaint->registration_number,
            $complaint->complaintType?->label,
            $complaint->status->label(),
            $complaint->current_level?->value,
            $complaint->channel?->label(),
            $complaint->site?->name,
            $complaint->site?->locality?->commune?->department?->region?->name,
            $complaint->subject,
            $complaint->created_at?->format('d/m/Y'),
            $complaint->sla_deadline?->format('d/m/Y'),
            $breached ? 'OUI' : 'NON',
            $complaint->registeredBy?->name,
        ];
    }
}
