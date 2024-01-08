@extends('layouts.crm')

@section('title', 'Истории')

@section('content')
    <x-alert></x-alert>
    <div class="row mb-4">
        <div class="col-12">
            <p>Рекомендуемы размер изображений 1080х1920 (9:16) - оптимально для отображения на мобильных</p>
            <button class="btn btn-outline-dark mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                Создать новую
                <i class="fa fa-plus px-1"></i>
            </button>
            <div class="collapse" id="collapseExample">
                <div class="card card-body">
                    <form action="{{ route('crm.stories.store') }}" method="post" enctype="multipart/form-data" id='story-form'>
                        @csrf
                        <div class="row row-cols-1 row-cols-md-2 align-items-start">
                            <div class="col mb-2 mb-md-0">
                                <div class="form-group mb-2">
                                    <label for="header">Заголовок</label>
                                    <input type="text" name="header" id="header" class="form-control" required>
                                </div>
                                <div class="form-group mb-4"> 
                                    <label for="image" class="btn btn-primary w-100">
                                        Добавить изображение
                                        <i class="fa fa-plus"></i>
                                        <input 
                                            onchange="{

                                                if (this.files[0].size > 2000000) {
                                                    alert('Размер файла не более 2 мегабайт')
                                                    return
                                                }
                                                document.querySelector('#image-preview').src = window.URL.createObjectURL(this.files[0])
                                                document.querySelector('button[type=submit]').disabled = false
                                            }"
                                            type="file" 
                                            name="image"  
                                            id="image" 
                                            style="display:none"
                                        >
                                    </label>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" disabled>Создать</button>
                                </div>
                            </div>
                            <div class="col">
                                <div style="max-width: 200px">
                                    <img src="" alt="" id="image-preview" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    @if ($stories->isEmpty())
        <div class="row">
            <div class="col">
                <p>Нет ни одной активной истории</p>
            </div>
        </div>
    @endif
    @foreach ($stories as $story)
    <div class="row row-cols row-cols-md-4">
            <div class="col mb-4">
                <div class="d-flex mb-2 position-relative rounded overflow-hidden bg-image align-items-start p-2 justify-content-end" style="min-height: 150px;background:url({{ $story->image->path }})">
                    <form action="{{ route('crm.stories.update', $story->id) }}" method="post"> @csrf @method('PATCH')
                        <button type="submit" class="btn-sm btn {{$story->status ? 'btn-warning' : 'btn-success'}}" style="margin-right: 6px">
                            {!! $story->status ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>' !!}
                        </button>
                    </form>
                    <form action="{{ route('crm.stories.destroy', $story->id) }}" method="post"> @csrf @method('delete')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Действие необратимо, нажмите OK для продолжения')"><i class="fa fa-trash"></i></button>
                    </form>
                </div>
                <h6>{{ $story->name }}</h6>
            </div>
        @endforeach
    </div>
@endsection