@extends('layouts.app')

@section('title', 'Registrar producto')

@section('content')
<div class="form-page">
    <h1 class="h3 mb-4">Registrar producto</h1>
    <div class="card"><div class="card-body">
        <form action="{{ route('productos.store') }}" method="POST">
            @csrf
            
            @include('productos._form', ['producto' => null])
        </form>
    </div></div>
</div>
@endsection
