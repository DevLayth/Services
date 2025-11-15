@php
    $path = request()->path();
@endphp

@switch($path)
    @case('/')
        @include('components.dashboard')
        @break

    @case('customers')
        @include('components.customers')
        @break

    @case('services')
        @include('components.services')
        @break
{{--
    @default
        @include('pages.404') --}}
@endswitch
