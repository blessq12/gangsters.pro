<section>
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <div class="section-title">
                    <h2>Категории</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <category-bar
                    :categories='@json($categories)'
                ></category-bar>
            </div>
        </div>
    </div>
</section>