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
                    <p class="text-warning">Сначала нужно ввести заголовок, а после добавить фото</p>
                    <form action="{{ route('crm.stories.store') }}" method="post" enctype="multipart/form-data" id='story-form'>
                        @csrf
                        <div class="row row-cols align-items-end">
                            <div class="col">
                                <label for="header">Заголовок</label>
                                <input type="text" name="header" id="header" class="form-control" required>
                            </div>
                            <div class="col">
                                <label for="image" class="btn btn-primary">
                                    Добавить изображение
                                    <i class="fa fa-plus"></i>
                                    <input type="file" name="image" id="image" style="display:none" onchange="document.querySelector('#story-form').submit()">
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <div class="row row-cols row-cols-md-4">
        @if ($stories->isEmpty())
            <div class="col-12">
                <p>Нет ни одной активной истории</p>
            </div>
        @endif
        @foreach ($stories as $story)
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