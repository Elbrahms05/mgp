<section>
    <p class="text-dark small mb-3">
        {{ __('Une fois supprimé, votre compte et l\'ensemble de ses données seront définitivement effacés. Cette action est irréversible.') }}
    </p>

    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
        <i data-feather="trash-2" class="me-1" style="width:14px;height:14px;"></i>
        {{ __('Supprimer mon compte') }}
    </button>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger-soft border-danger">
                    <h5 class="modal-title text-danger fw-bold" id="confirmUserDeletionLabel">
                        <i data-feather="alert-triangle" class="me-2" style="width:18px;height:18px;"></i>
                        {{ __('Supprimer définitivement votre compte') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-body">
                        <p class="text-dark fw-bold mb-3">
                            {{ __('Êtes-vous absolument certain ?') }}
                        </p>
                        <p class="text-muted small mb-4">
                            {{ __('Cette action supprimera définitivement votre compte et toutes les données associées. Cette opération ne peut pas être annulée. Veuillez entrer votre mot de passe pour confirmer.') }}
                        </p>

                        <div>
                            <label for="delete-password" class="form-label small">{{ __('Mot de passe de confirmation') }}</label>
                            <input
                                id="delete-password"
                                name="password"
                                type="password"
                                class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                                placeholder="{{ __('Entrez votre mot de passe') }}"
                                autofocus
                            />
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            {{ __('Annuler') }}
                        </button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i data-feather="trash-2" class="me-1" style="width:14px;height:14px;"></i>
                            {{ __('Oui, supprimer définitivement') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
