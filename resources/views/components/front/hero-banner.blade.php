<div class="relative w-full bg-cover bg-center bg-no-repeat" style="background-image: url({{ $banner }})">
    @if ($overlay)
        <div class="absolute inset-0 bg-black/50"></div>
    @endif
    <div class="container relative mx-auto px-4">
        <div class="flex min-h-[400px] items-center justify-center">
            <div class="max-w-3xl text-center">
                <h1 class="mb-4 text-4xl font-bold text-white md:text-5xl lg:text-6xl">{{ $title }}</h1>
                <p class="text-lg text-white/90 md:text-xl">{{ $description }}</p>
            </div>
        </div>
    </div>
</div>
