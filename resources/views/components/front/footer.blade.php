<footer>
    <div class="container">
        <div class="row">
            <a href="{{ route('main.index') }}">
                <div class="footer-logo">
                    <img src="{{ $company->logo ? $company->logo->path : 'http://via.placeholder.com/50x50' }}" alt="{{ $company->name }}">
                    <span>
                        {{ $company->name }}
                    </span>
                </div>
            </a>
        </div>
    </div>
</footer>