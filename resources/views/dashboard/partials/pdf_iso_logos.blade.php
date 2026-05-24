@php
    $isoLogosPath = public_path('assets/images/ISO_logos.jpg');
    $isoLogosDataUri = is_readable($isoLogosPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($isoLogosPath))
        : null;
@endphp
@if ($isoLogosDataUri)
    <div class="pdf-iso-logos">
        <img src="{{ $isoLogosDataUri }}" alt="ISO 9001, ISO 14001, ISO 45001" class="pdf-iso-logos-img">
    </div>
@endif
