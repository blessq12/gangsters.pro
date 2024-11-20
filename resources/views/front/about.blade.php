@extends('layouts.front')

@section('title', 'О компании')
@section('desc', '')

@section('hero')
    <x-front.hero-banner
        title="О компании"
        description="Информация о нашей компании"
        :overlay="true"
    ></x-front.hero-banner>
@endsection

@section('content')
    <div class="container py-5">
    <div class="row mb-4 py-2 py-lg-4">
        <div class="col-lg-6">
            <h4 class="mb-3" style="font-weight: 500;">Наша история</h4>
            <p>Основана 1 июля 2019 года, наша компания стремится предоставлять высококачественные услуги и продукты, соответствующие самым высоким стандартам.</p>
            <p>
            Гангста суши - это не только доставка еды, это уникальная и культовая доставка, которая умело сочетает в себе изысканный вкус паназиатской кухни и неподражаемый брутальный гангстерский дух. В нашем разнообразном меню вы найдете широкий ассортимент роллов, воков, закусок и множество других блюд, каждое из которых приготовлено из свежайших ингредиентов наивысшего качества. Мы обеспечиваем скоростную доставку и высококлассное обслуживание, гарантируя, что каждый наш клиент сможет насладиться превосходным вкусом наших блюд без каких-либо затруднений и неудобств.
            <br>
            Наши постоянные акции:
            <ul>
                <li>Ко дню рождения получи ролл икура в подарок или скидку 15%.</li>
                <li>При самовывозе ролл в подарок на выбор.</li>
            </ul>
            </p>
        </div>
        <div class="col-lg-6">
        <div class="logo-block h-100" style="background-image: url({{ asset('/uploads/' . $company->logo) }}); background-size: contain; background-position: center; background-repeat: no-repeat;">
            
        </div>
        </div>
    </div>
    <div class="row mb-4 py-2 py-lg-4">
        <div class="col-12 mb-2">
            <h4 class="mb-3" style="font-weight: 500;">Наши ценности</h4>
        </div>
        <div class="col-12">
            <div class="row row-cols-1 row-cols-lg-4 g-4" id="about-blocks">
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Клиентоориентированность</h5>
                        <p class="card-text">Мы всегда ставим потребности клиента на первое место.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Инновации</h5>
                        <p class="card-text">Мы постоянно ищем новые идеи и технологии для улучшения наших услуг.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ответственность</h5>
                        <p class="card-text">Мы принимаем на себя ответственность за качество наших продуктов и услуг.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Профессионализм</h5>
                        <p class="card-text">Мы стремимся к высочайшему уровню профессионализма во всем, что мы делаем.</p>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection