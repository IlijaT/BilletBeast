@extends('layouts.master')

@section('content')
<div class="bg-soft p-xs-y-7 full-height">
    <div class="container">
        {{-- @if ($concert->hasPoster())
            @include('concerts.partials.card-with-poster', ['concert' => $concert])
        @else
            @include('concerts.partials.card-no-poster', ['concert' => $concert])
        @endif --}}
        @include('concerts.partials.card-no-poster', ['concert' => $concert])
    </div>
</div>
@endsection

@push('beforeScripts')
    <script src="https://checkout.stripe.com/checkout.js"></script>
@endpush