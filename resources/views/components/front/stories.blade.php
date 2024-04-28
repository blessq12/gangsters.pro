<section>
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <div class="section-title">
                    <h2>{{ __('Актуальное') }}</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <front-stories :stories='@json($stories)'></front-stories>
            </div>
        </div>
    </div>
</section>