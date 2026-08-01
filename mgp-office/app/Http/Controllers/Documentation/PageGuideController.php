<?php

declare(strict_types=1);

namespace App\Http\Controllers\Documentation;

use App\Domains\Documentation\Models\PageGuide;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documentation\StorePageGuideRequest;
use App\Http\Requests\Documentation\UpdatePageGuideRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageGuideController extends Controller
{
    public function index(): View
    {
        $guides = PageGuide::orderBy('route_name')->paginate(30);

        return view('pages.documentation.index', compact('guides'));
    }

    public function create(): View
    {
        return view('pages.documentation.create');
    }

    public function store(StorePageGuideRequest $request): RedirectResponse
    {
        $data = $request->validated();

        PageGuide::create($data);

        return redirect()
            ->route('documentation.index')
            ->with('success', "Guide créé pour la route « {$data['route_name']} ».");
    }

    public function edit(PageGuide $guide): View
    {
        return view('pages.documentation.edit', compact('guide'));
    }

    public function update(UpdatePageGuideRequest $request, PageGuide $guide): RedirectResponse
    {
        $data = $request->validated();

        $guide->update($data);

        return redirect()
            ->route('documentation.index')
            ->with('success', "Guide « {$guide->route_name} » mis à jour.");
    }

    public function destroy(PageGuide $guide): RedirectResponse
    {
        $route = $guide->route_name;
        $guide->delete();

        return redirect()
            ->route('documentation.index')
            ->with('success', "Guide « {$route} » supprimé.");
    }
}
