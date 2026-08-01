{{--
  Affiche un champ chiffré avec indicateur visuel.
  Props : $value (valeur déchiffrée ou null), $canDecrypt (bool), $label (string)
--}}
@props(['value' => null, 'canDecrypt' => false, 'label' => 'Champ chiffré'])

@if($canDecrypt && $value)
    <span class="vbg-encrypted-field d-inline-flex align-items-center gap-1">
        {{ $value }}
        <i data-feather="unlock" style="width:13px;height:13px;color:#2e7d32;"
           title="Déchiffré - accès autorisé"></i>
    </span>
@else
    <span class="text-muted fst-italic d-inline-flex align-items-center gap-1"
          style="font-size:0.85rem;">
        <i data-feather="lock" style="width:13px;height:13px;"></i>
        [Chiffré - accès restreint]
    </span>
@endif
