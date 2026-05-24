@php
    $pdfLogoPath = public_path('assets/images/arabian_dena_logo.jpeg');
    $pdfLogoDataUri = is_readable($pdfLogoPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($pdfLogoPath))
        : null;
@endphp
@if ($pdfLogoDataUri)
    <img src="{{ $pdfLogoDataUri }}" alt="Arabian Dena Contracting Est." class="pdf-logo">
@endif
