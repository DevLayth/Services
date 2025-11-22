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

    @case('subscription')
        @include('components.subscription')
        @break


    @case('currencies')
        @include('components.currencies')
        @break

    @case('paid-invoices')
        @include('components.paid-invoices')
        @break

    @case('accounts')
        @include('components.accounts')
        @break

    @case('journal-entries')
        @include('components.journal-entries')
        @break

    @default
        @include('components.404')


@endswitch
