<x-layout.app
    title="Documentation"
    description="Guides contextuels - chaque route a son guide MGP obligatoire"
    icon="book-open">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Documentation</li>
    </x-slot>

    @can('documentation.manage')
    <x-slot name="actions">
        <a href="{{ route('documentation.create') }}" class="btn btn-success btn-sm">
            <i data-feather="plus" style="width:16px;height:16px;"></i> Nouveau guide
        </a>
    </x-slot>
    @endcan

    <div class="card margin-row">
        <div class="card-body p-0">
            @if($guides->isEmpty())
            <div class="text-center text-muted" style="padding:48px;">
                <i data-feather="help-circle" style="width:48px;height:48px;display:block;margin:0 auto 16px;"></i>
                <p>Aucun guide contextuel.<br>
                   Exécutez <code>php artisan db:seed --class=PageGuidesSeeder</code></p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th>Titre page</th>
                            <th>Réf. MGP</th>
                            <th>Mode</th>
                            <th class="text-center">Actif</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guides as $guide)
                        <tr>
                            <td><code style="font-size:0.78rem;">{{ $guide->route_name }}</code></td>
                            <td>{{ $guide->page_title }}</td>
                            <td class="text-muted">{{ $guide->mgp_reference }}</td>
                            <td>
                                <span class="badge bg-primary" style="font-size:0.7rem;">
                                    {{ $guide->display_mode }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($guide->is_active)
                                <i data-feather="check-circle" style="width:16px;height:16px;color:var(--pacipa-green);"></i>
                                @else
                                <i data-feather="x-circle" style="width:16px;height:16px;color:var(--pacipa-grey);"></i>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('documentation.manage')
                                <a href="{{ route('documentation.edit', $guide) }}" class="btn btn-sm btn-outline-warning">
                                    <i data-feather="edit-2" style="width:14px;height:14px;"></i>
                                </a>
                                <form method="POST" action="{{ route('documentation.destroy', $guide) }}" style="display:inline;"
                                      onsubmit="return confirm('Supprimer ce guide ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @if($guides->hasPages())
        <div class="card-footer">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    @if ($guides->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">← Précédent</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $guides->previousPageUrl() }}" rel="prev">← Précédent</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @for ($page = 1; $page <= $guides->lastPage(); $page++)
                        @if ($page == $guides->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $guides->url($page) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endfor

                    {{-- Next Page Link --}}
                    @if ($guides->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $guides->nextPageUrl() }}" rel="next">Suivant →</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">Suivant →</span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>

</x-layout.app>
