<div class="row mt-5 mb-5">
    @extends('home::layouts.master')

    @section('content')
        <h1>Hello World</h1>

        <p>Module: {!! config('home.name') !!}</p>
    @endsection
</div>
