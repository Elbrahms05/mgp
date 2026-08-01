<?php

declare(strict_types=1);

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reports.generate');
    }

    public function rules(): array
    {
        return [
            'report_type' => ['required', 'string', 'in:monthly_dashboard,quarterly_vbg,committee_evaluation,complaint_detail,sla_compliance,annual_mgp'],
            'date_from'   => ['required', 'date'],
            'date_to'     => ['required', 'date', 'after_or_equal:date_from'],
            'format'      => ['required', 'string', 'in:csv,html'],
        ];
    }

    public function attributes(): array
    {
        return [
            'report_type' => 'type de rapport',
            'date_from'   => 'date de début',
            'date_to'     => 'date de fin',
            'format'      => 'format d\'export',
        ];
    }
}
