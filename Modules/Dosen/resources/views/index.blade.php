<div class="row mt-5 mb-5">
    @extends('dosen::layouts.master')

    @section('content')
        <h1>Hello World</h1>

        <p>Module: {!! config('dosen.name') !!}</p>
    @endsection
</div>
