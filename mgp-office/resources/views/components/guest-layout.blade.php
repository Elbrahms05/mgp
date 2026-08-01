@props(['title' => null])
<x-layout.auth :title="$title ?? null">
    {{ $slot }}
</x-layout.auth>
