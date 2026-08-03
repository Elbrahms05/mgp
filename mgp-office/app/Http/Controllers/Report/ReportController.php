<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Domains\Committee\Models\Committee;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Vbg\Models\VbgReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\GenerateReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** Types de rapports disponibles */
    private const REPORT_TYPES = [
        'monthly_dashboard'   => 'Tableau de bord mensuel',
        'quarterly_vbg'       => 'Rapport VBG agrégé trimestriel',
        'committee_evaluation'=> 'Évaluation des comités',
        'complaint_detail'    => 'Rapport détaillé plaintes',
        'sla_compliance'      => 'Conformité SLA',
        'annual_mgp'          => 'Rapport annuel MGP',
    ];

    public function index(): View
    {
        return view('pages.reports.index', [
            'reportTypes' => self::REPORT_TYPES,
        ]);
    }

    public function generate(GenerateReportRequest $request): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        $data = $request->validated();

        $from = \Carbon\Carbon::parse($data['date_from'])->startOfDay();
        $to   = \Carbon\Carbon::parse($data['date_to'])->endOfDay();

        // Export PDF (états mensuel / VBG agrégé / annuel) - EF-14 / ER-04
        if ($data['format'] === 'pdf') {
            if ($data['report_type'] === 'quarterly_vbg'
                && ! $request->user()->can('statistics.view_vbg_aggregated')) {
                return back()->with('error', 'Accès refusé : rapport VBG réservé au Coordonnateur UGP et Spécialiste VBG.');
            }

            $pdf = app(\App\Domains\Statistic\Services\ReportPdfService::class)->forType($data['report_type'], $from, $to);

            if ($pdf === null) {
                return back()->with('error', "Le format PDF n'est pas disponible pour ce type de rapport.");
            }

            return $pdf->download("rapport_{$data['report_type']}_{$from->format('Ymd')}_{$to->format('Ymd')}.pdf");
        }

        // Export Excel des plaintes non sensibles
        if ($data['format'] === 'xlsx') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Domains\Statistic\Exports\ComplaintsExport($from, $to),
                "rapport_plaintes_{$from->format('Ymd')}_{$to->format('Ymd')}.xlsx",
            );
        }

        return match ($data['report_type']) {
            'monthly_dashboard', 'complaint_detail', 'sla_compliance', 'annual_mgp'
                => $this->generateComplaintReport($data['report_type'], $from, $to, $data['format'], $request),
            'quarterly_vbg'
                => $this->generateVbgReport($from, $to, $data['format'], $request),
            'committee_evaluation'
                => $this->generateCommitteeReport($from, $to, $data['format'], $request),
            default
                => back()->with('error', 'Type de rapport non reconnu.'),
        };
    }

    /**
     * Re-generate and stream a report from query-string parameters.
     * The {report} segment is the report_type slug; date_from, date_to, format come from the query string.
     * The route is protected by a signed URL so parameters cannot be tampered with.
     */
    public function download(Request $request, string $report): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Lien de téléchargement invalide ou expiré.');
        }

        if (! array_key_exists($report, self::REPORT_TYPES)) {
            abort(404, 'Type de rapport inconnu.');
        }

        $from   = \Carbon\Carbon::parse($request->query('date_from'))->startOfDay();
        $to     = \Carbon\Carbon::parse($request->query('date_to'))->endOfDay();
        $format = in_array($request->query('format'), ['csv', 'html'], true)
            ? $request->query('format')
            : 'csv';

        return match ($report) {
            'monthly_dashboard', 'complaint_detail', 'sla_compliance', 'annual_mgp'
                => $this->generateComplaintReport($report, $from, $to, $format, $request),
            'quarterly_vbg'
                => $this->generateVbgReport($from, $to, $format, $request),
            'committee_evaluation'
                => $this->generateCommitteeReport($from, $to, $format, $request),
            default => back()->with('error', 'Type de rapport non reconnu.'),
        };
    }

    private function generateComplaintReport(
        string $type,
        \Carbon\Carbon $from,
        \Carbon\Carbon $to,
        string $format,
        Request $request
    ): \Symfony\Component\HttpFoundation\Response {
        $complaints = Complaint::with(['complaintType', 'site.locality.commune.department.region', 'registeredBy'])
            ->whereBetween('created_at', [$from, $to])
            ->when($type === 'sla_compliance', fn ($q) => $q->whereNotNull('sla_deadline'))
            ->orderByDesc('created_at')
            ->get();

        if ($format === 'html') {
            return response()->view('pages.reports.exports.complaints', compact('complaints', 'from', 'to', 'type'));
        }

        // CSV export
        $filename = "rapport_{$type}_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($complaints, $type) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'N° Plainte', 'Type', 'Statut', 'Canal', 'Site', 'Région',
                'Objet', 'Date faits', 'Date enregistrement', 'Échéance SLA',
                'SLA Dépassé', 'Enregistré par',
            ], ';');

            foreach ($complaints as $c) {
                fputcsv($handle, [
                    $c->registration_number,
                    $c->complaintType?->label ?? '-',
                    $c->status->label(),
                    $c->channel?->label() ?? '-',
                    $c->site?->name ?? 'Hors site',
                    $c->site?->locality?->commune?->department?->region?->name ?? '-',
                    $c->subject,
                    $c->incident_date?->format('d/m/Y') ?? '-',
                    $c->created_at->format('d/m/Y H:i'),
                    $c->sla_deadline?->format('d/m/Y H:i') ?? '-',
                    ($c->sla_deadline && $c->sla_deadline->isPast() && !in_array($c->status->value, [ComplaintStatus::CLOSED->value, ComplaintStatus::REJECTED->value])) ? 'OUI' : 'non',
                    $c->registeredBy?->name ?? '-',
                ], ';');
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function generateVbgReport(
        \Carbon\Carbon $from,
        \Carbon\Carbon $to,
        string $format,
        Request $request
    ): \Symfony\Component\HttpFoundation\Response|RedirectResponse {
        // Vérification accès VBG agrégé
        if (! $request->user()->can('statistics.view_vbg_aggregated')) {
            return back()->with('error', 'Accès refusé : rapport VBG réservé au Coordonnateur UGP et Spécialiste VBG.');
        }

        $total     = VbgReport::whereBetween('created_at', [$from, $to])->count();
        $anonymous = VbgReport::whereBetween('created_at', [$from, $to])->where('is_anonymous', true)->count();
        $referred  = VbgReport::whereBetween('created_at', [$from, $to])->whereHas('referrals')->count();
        $byChannel = VbgReport::whereBetween('created_at', [$from, $to])
            ->selectRaw('channel, count(*) as total')
            ->groupBy('channel')
            ->get()
            ->pluck('total', 'channel')
            ->toArray();

        $stats = compact('total', 'anonymous', 'referred', 'byChannel', 'from', 'to');

        if ($format === 'html') {
            return response()->view('pages.reports.exports.vbg_aggregated', $stats);
        }

        // CSV agrégé (JAMAIS de données individuelles)
        $filename = "rapport_vbg_agrege_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($stats) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Indicateur', 'Valeur'], ';');
            fputcsv($handle, ['Période', $stats['from']->format('d/m/Y') . ' - ' . $stats['to']->format('d/m/Y')], ';');
            fputcsv($handle, ['Total signalements', $stats['total']], ';');
            fputcsv($handle, ['Signalements anonymes', $stats['anonymous']], ';');
            fputcsv($handle, ['Référencés services', $stats['referred']], ';');
            fputcsv($handle, ['Taux référencement (%)', $stats['total'] > 0 ? round(($stats['referred'] / $stats['total']) * 100, 1) : 0], ';');
            foreach ($stats['byChannel'] as $channel => $count) {
                fputcsv($handle, ["Canal : {$channel}", $count], ';');
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function generateCommitteeReport(
        \Carbon\Carbon $from,
        \Carbon\Carbon $to,
        string $format,
        Request $request
    ): \Symfony\Component\HttpFoundation\Response {
        $committees = Committee::with(['locality.commune.department.region', 'members'])
            ->withCount('members')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        if ($format === 'html') {
            return response()->view('pages.reports.exports.committees', compact('committees', 'from', 'to'));
        }

        $filename = "rapport_comites_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($committees) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Comité', 'Niveau', 'Localité', 'Région', 'Membres', 'Actif', 'Date install.'], ';');
            foreach ($committees as $c) {
                fputcsv($handle, [
                    $c->name,
                    strtoupper($c->level->value ?? $c->level),
                    $c->locality?->name ?? '-',
                    $c->locality?->commune?->department?->region?->name ?? '-',
                    $c->members_count,
                    $c->is_active ? 'Oui' : 'Non',
                    $c->installation_date?->format('d/m/Y') ?? '-',
                ], ';');
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
