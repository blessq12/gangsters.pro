@extends('layouts.crm')

@section('title', 'Категории товаров')

@section('content')
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4">
        @foreach ($categories as $category)
            <div class="col mb-4">
                <a href="{{ route('crm.products.showCategory', $category->id) }}" class="text-decoration-none text-dark">
                    <div class="d-flex align-items-end border border-dark px-2 py-2 rounded" style="min-height: 120px">
                        <h6>{{ $category->name }}</h6>
                    </div>
                </a>
            </div>
        @endforeach

    </div>
@endsection