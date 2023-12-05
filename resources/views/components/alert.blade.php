
@if (session('success'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
    </div>    
@endif

@if (session('warning'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                {{ session('warning') }}
            </div>
        </div>
    </div>    
@endif

@if ($errors->any())
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger">
                <ul class="list-unstyled p-0 m-0">
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif