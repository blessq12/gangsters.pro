@extends('layouts.front')

@section('title', $document->title)
@section('desc', '')

@section('hero')
    <x-front.hero-banner :title="$document->title"
        description="Актуальная версия юридического документа"
        :overlay="true" banner="/images/hero-bg.webp"></x-front.hero-banner>
@endsection

@section('content')
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4 max-w-4xl">
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
