<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Statistic\Exports\ComplaintsExport;
use App\Domains\Statistic\Services\ReportPdfService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * EF-05 / EF-14 / ER-04 - Génération des états : PDF (tableau de bord, VBG agrégé) et Excel.
 */
class ReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_tableau_de_bord_mensuel_est_genere_en_pdf(): void
    {
        $pdf = app(ReportPdfService::class)
            ->monthlyDashboard(now()->startOfMonth(), now()->endOfMonth())
            ->output();

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(1000, strlen($pdf));
    }

    public function test_le_rapport_vbg_agrege_est_genere_en_pdf(): void
    {
        $pdf = app(ReportPdfService::class)
            ->quarterlyVbgAggregate(now()->startOfQuarter(), now()->endOfQuarter())
            ->output();

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_l_export_excel_des_plaintes_produit_un_fichier(): void
    {
        $binary = Excel::raw(
            new ComplaintsExport(Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31')),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $this->assertNotEmpty($binary);
        // Signature d'un fichier ZIP/xlsx : "PK"
        $this->assertStringStartsWith('PK', $binary);
    }
}
