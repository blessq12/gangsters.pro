@extends('layouts.crm')

@section('title', 'Категория - ' . $category->name )

@section('content')
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4">
        @foreach ($products as $product)
        <div class="col mb-4">
            <a href="#" >
                <div class="border border-primary p-3 rounded d-flex align-items-end" style="min-height: 120px">
                    {{ $product->name }}
                </div>
            </a>
        </div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-12">
            {{ $products->links() }}
        </div>
    </div>
@endsection