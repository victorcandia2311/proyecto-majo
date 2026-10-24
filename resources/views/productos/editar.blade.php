@extends('layouts.app')

@section('title', 'Editar producto')

@section('content')
<div class="form-page">
    <h1 class="h3 mb-4">Editar producto</h1>
    <div class="card"><div class="card-body">
        <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('productos._form', ['producto' => $producto])
        </form>
    </div></div>
</div>
@endsection
