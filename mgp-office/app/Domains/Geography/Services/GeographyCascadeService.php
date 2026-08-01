<?php

declare(strict_types=1);

namespace App\Domains\Geography\Services;

use App\Domains\Geography\Models\Commune;
use App\Domains\Geography\Models\Department;
use App\Domains\Geography\Models\Locality;
use App\Domains\Geography\Models\Region;
use Illuminate\Database\Eloquent\Collection;

class GeographyCascadeService
{
    public function getRegions(): Collection
    {
        return Region::orderBy('name')->get(['id', 'code', 'name']);
    }

    public function getDepartments(int $regionId): Collection
    {
        return Department::where('region_id', $regionId)
            ->orderBy('name')
            ->get(['id', 'region_id', 'code', 'name']);
    }

    public function getCommunes(int $departmentId): Collection
    {
        return Commune::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'department_id', 'code', 'name']);
    }

    public function getLocalities(int $communeId): Collection
    {
        return Locality::where('commune_id', $communeId)
            ->orderBy('name')
            ->get(['id', 'commune_id', 'code', 'name']);
    }
}
