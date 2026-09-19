@extends('layouts.front')

@section('title', $document->title)
@section('desc', '')

@section('hero')
    <x-front.hero-banner :title="$document->title"
        description="Реквизиты продавца и информация для потребителя"
        :overlay="true" banner="/images/hero-bg.webp"></x-front.hero-banner>
@endsection

@section('content')
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4 max-w-4xl space-y-8">
            @if ($legals)
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Реквизиты продавца</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700">
                        @foreach ($legals as $label => $value)
                            <div>
                                <dt class="text-sm text-gray-500">{{ $label }}</dt>
                                <dd class="font-medium">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="mt-6 text-sm text-gray-500">
                        Подробнее об оплате и доставке:
                        <a href="{{ route('main.purchaseAndDelivery') }}" class="text-blue-600 underline">Оплата и доставка</a>
                    </p>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="prose prose-lg max-w-none legal-document-body">
                    {!! $document->body_html !!}
                </div>
                <p class="mt-8 text-sm text-gray-400">Версия {{ $document->version }} · опубликовано
                    {{ optional($document->published_at)->format('d.m.Y') }}</p>
            </div>
        </div>
    </section>
@endsection
