@extends('layouts.crm')

@section('title', 'Товар')

@section('content')
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4">
        @foreach ($categories as $category)
            <div class="col mb-4">
                <a href="{{ route('crm.products.show', $category->id) }}">
                    <div class="d-flex align-items-end border border-primary p-3 rounded" style="min-height: 120px">
                        {{ $category->name }}
                    </div>
                </a>
            </div>
        @endforeach

    </div>
@endsection