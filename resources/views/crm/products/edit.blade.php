@extends('layouts.crm')
@section('title', 'Редактирование ' . $product->name)

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col d-flex">

        
            <form action="{{ route('crm.image.product-image-upload') }}" method="post" enctype="multipart/form-data" id="upload">
                @csrf
                <input type="hidden" name="prod_id" value="{{ $product->id }}">
                <label for="image" class="btn btn-secondary d-flex align-items-center justify-content-center" style="height: 120px; width: 120px;margin-right: 12px;">
                    <i class="fa fa-plus fa-3x"></i>
                    <input type="file" name="images[]" id="image" style="display: none" onchange="document.querySelector('#upload').submit()" multiple>
                </label>
            </form>
            @if (!$product->images->isEmpty())
                <ul class="list-unstyled d-flex">
                    @foreach ($product->thumbMedium as $e)
                        <li class="bg-image rounded p-2 d-flex justify-content-end" style="height: 120px; width: 120px;margin-right: 12px;background: url({{ $e->path }})">
                            <form action="{{ route('crm.image.image-destroy', $e->id) }}" method="post">
                                @csrf @method('delete') 
                                <input type="hidden" name="instance" value="product">
                                <input type="hidden" name="instance" value="product">
                                <button class="btn-sm btn-danger btn"><i class="fa fa-trash"></i></button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="rounded border border-secondary h-100 text-secondary p-3 d-flex align-items-center" >
                    Нажмите "+" чтобы добавить фотографии
                </div>
            @endif
        </div>
    </div>

@endsection