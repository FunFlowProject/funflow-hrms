@props([
    'src' => asset('assets/img/ff-logo-icon.png'),
    'alt' => config('app.name', 'Funflow HRMS'),
    'size' => 42,
    'imgClass' => '',
    'text' => config('app.name', 'Funflow HRMS'),
    'textClass' => 'menu-text fw-bolder ms-2',
    'showText' => false,
])

<span class="app-brand-logo demo">
    <img src="{{ $src }}" alt="{{ $alt }}" class="{{ trim('funflow-logo-image ' . $imgClass) }}"
        style="height: {{ (int) $size }}px; width: auto;" decoding="async" fetchpriority="high" />
</span>
