<div class="row mt-5 mb-5">
    @extends('home::layouts.master')

    @section('content')
        {{-- Gambar --}}
        <img src="{{ asset('functional.jpeg') }}" alt="" class="w-100 rounded mb-5">
        <img src="{{ asset('professional.jpeg') }}" alt="" class="w-100 rounded mb-5">
    @endsection
</div>
