@extends('layouts.front')

@section('title', 'О компании')
@section('desc', '')

@section('hero')
    <x-front.hero-banner title="О компании" description="Информация о нашей компании" :overlay="true"
        banner='/images/hero-bg.webp'></x-front.hero-banner>
@endsection

@section('content')
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 py-4 lg:py-8">
            <div>
                <h4 class="text-2xl font-medium mb-6">Наша история</h4>
                <p class="mb-4">Основана 1 июля 2019 года, наша компания стремится предоставлять высококачественные услуги
                    и продукты, соответствующие самым высоким стандартам.</p>
                <div class="space-y-4">
                    <p>
                        Гангста суши - это не только доставка еды, это уникальная и культовая доставка, которая умело
                        сочетает в себе изысканный вкус паназиатской кухни и неподражаемый брутальный гангстерский дух. В
                        нашем разнообразном меню вы найдете широкий ассортимент роллов, воков, закусок и множество других
                        блюд, каждое из которых приготовлено из свежайших ингредиентов наивысшего качества. Мы обеспечиваем
                        скоростную доставку и высококлассное обслуживание, гарантируя, что каждый наш клиент сможет
                        насладиться превосходным вкусом наших блюд без каких-либо затруднений и неудобств.
                    </p>
                    <div>
                        <p class="mb-2">Наши постоянные акции:</p>
                        <ul class="list-disc pl-6 space-y-2">
                            <li>Ко дню рождения получи ролл икура в подарок или скидку 15%.</li>
                            <li>При самовывозе ролл в подарок на выбор.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="h-full">
                <div class="h-full bg-no-repeat bg-center bg-contain"
                    style="background-image: url({{ asset('/uploads/' . $company->logo) }})">
                </div>
            </div>
        </div>

        <div class="mb-8 py-4 lg:py-8">
            <div class="mb-6">
                <h4 class="text-2xl font-medium mb-6">Наши ценности</h4>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6" id="about-blocks">
                <div class="bg-white rounded-lg shadow-md p-6 h-full">
                    <h5 class="text-xl font-medium mb-3">Наши клиенты</h5>
                    <p class="text-gray-600">Мы всегда ставим потребности клиента на первое место.</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 h-full">
                    <h5 class="text-xl font-medium mb-3">Инновации</h5>
                    <p class="text-gray-600">Мы постоянно ищем новые идеи и технологии для улучшения наших услуг.</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 h-full">
                    <h5 class="text-xl font-medium mb-3">Ответственность</h5>
                    <p class="text-gray-600">Мы принимаем на себя ответственность за качество наших продуктов и услуг.</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 h-full">
                    <h5 class="text-xl font-medium mb-3">Профессионализм</h5>
                    <p class="text-gray-600">Мы стремимся к высочайшему уровню профессионализма во всем, что мы делаем.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
