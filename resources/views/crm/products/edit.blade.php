@extends('layouts.crm')
@section('title', 'Редактирование ' . $product->name)

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @php
            $images = [
                (object) ['id' => 1, 'path' => '//via.placeholder.com/512x512'],
                (object) ['id' => 2, 'path' => '//via.placeholder.com/512x512'],
                (object) ['id' => 3, 'path' => '//via.placeholder.com/512x512'],
                (object) ['id' => 4, 'path' => '//via.placeholder.com/512x512'],
            ]
        @endphp
        @if (!$images)
            <ul class="list-unstyled d-flex">
                @foreach ($images as $e)
                <li
                class="bg-image rounded"
                style="background: url( {{ $e->path }} ); height: 120px; width: 120px; margin-right: 12px"
                ></li>
                @endforeach
            </ul>
        @else
            <form action="{{ route('crm.image.product-image-upload') }}" method="post" enctype="multipart/form-data" id="upload">
                @csrf
                <input type="hidden" name="prod_id" value="{{ $product->id }}">
                <label for="image" class="btn btn-secondary d-flex align-items-center justify-content-center" style="height: 120px; width: 120px;">
                    <i class="fa fa-plus fa-3x"></i>
                    <input type="file" name="images[]" id="image" style="display: none" onchange="document.querySelector('#upload').submit()" multiple>
                </label>
            </form>
        @endif
    </div>

@endsection