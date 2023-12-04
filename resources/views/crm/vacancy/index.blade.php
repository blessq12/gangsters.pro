@extends('layouts.crm')

@section('title', 'Вакансии')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="accordion">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#create">
                      Создать вакансию
                    </button>
                  </h2>
                  <div id="create" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        Форма для создания новой вакансии
                    </div>
                  </div>
                </div>
              </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            @if ($vacancies->isEmpty())
                <p>На данные момент нет никаких активных вакансий</p>
            @endif
        </div>
    </div>
@endsection