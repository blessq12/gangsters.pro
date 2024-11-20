<div class="hero-banner" style="background: url({{ $banner }})">
    @if ($overlay)
        <div class="overlay"></div>
    @endif
    <div class="container position-relative">
        <div class="row">
            <div class="col">
                <div class="content">
                    <h1>{{ $title }}</h1>
                    <p class="mb-0"> {{ $description }} </p>
                </div>
            </div>
        </div>
    </div>
</div>
