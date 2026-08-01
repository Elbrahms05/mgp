<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Domains\Company\Models\Company;
use App\Domains\Site\Models\Site;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $companies = Company::query()
            ->withCount('sites')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('number_registre', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            })
            ->when($request->filled('sector'), function ($q) use ($request) {
                $q->where('sector', $request->input('sector'));
            })
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', $request->input('is_active') === 'true');
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        $sectors = Company::query()
            ->distinct()
            ->pluck('sector')
            ->filter()
            ->sort();

        return view('pages.companies.index', compact('companies', 'sectors'));
    }

    public function create(): View
    {
        return view('pages.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|unique:companies',
            'number_registre'   => 'nullable|string|unique:companies',
            'phone'             => 'nullable|string',
            'email'             => 'nullable|email',
            'address'           => 'nullable|string',
            'website'           => 'nullable|url',
            'sector'            => 'nullable|string',
            'is_active'         => 'boolean',
        ]);

        $company = Company::create($validated);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Entreprise créée avec succès.');
    }

    public function show(Company $company): View
    {
        $company->load(['sites' => function ($q) {
            $q->with('locality.commune.department.region');
        }]);

        $company->loadCount('sites');

        $availableSites = Site::query()
            ->with('locality.commune.department.region')
            ->whereDoesntHave('companies', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->orderBy('name')
            ->get();

        return view('pages.companies.show', compact('company', 'availableSites'));
    }

    public function edit(Company $company): View
    {
        return view('pages.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'              => 'required|string|unique:companies,name,' . $company->id,
            'number_registre'   => 'nullable|string|unique:companies,number_registre,' . $company->id,
            'phone'             => 'nullable|string',
            'email'             => 'nullable|email',
            'address'           => 'nullable|string',
            'website'           => 'nullable|url',
            'sector'            => 'nullable|string',
            'is_active'         => 'boolean',
        ]);

        $company->update($validated);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Entreprise mise à jour avec succès.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Entreprise supprimée avec succès.');
    }

    /**
     * Attach companies to a site with responsible contact
     */
    public function attachSite(Request $request, Company $company)
    {
        $validated = $request->validate([
            'site_id'               => 'required|exists:sites,id',
            'responsible_name'      => 'required|string',
            'responsible_phone'     => 'nullable|string',
            'responsible_email'     => 'nullable|email',
        ]);

        $company->sites()->attach(
            $validated['site_id'],
            [
                'responsible_name'  => $validated['responsible_name'],
                'responsible_phone' => $validated['responsible_phone'] ?? null,
                'responsible_email' => $validated['responsible_email'] ?? null,
            ]
        );

        return redirect()->back()->with('success', 'Site attaché à l\'entreprise.');
    }

    /**
     * Detach company from site
     */
    public function detachSite(Company $company, Site $site)
    {
        $company->sites()->detach($site->id);

        return redirect()->back()->with('success', 'Site détaché de l\'entreprise.');
    }

    /**
     * Update responsible contact for a site
     */
    public function updateResponsible(Request $request, Company $company, Site $site)
    {
        $validated = $request->validate([
            'responsible_name'      => 'required|string',
            'responsible_phone'     => 'nullable|string',
            'responsible_email'     => 'nullable|email',
        ]);

        $company->sites()->updateExistingPivot($site->id, $validated);

        return redirect()->back()->with('success', 'Responsable mis à jour.');
    }
}
