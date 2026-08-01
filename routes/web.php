<?php

use Illuminate\Support\Facades\Route;

Route::get('/images/og/{asset}', function (string $asset) {
    $safeName = basename($asset);
    if ($safeName !== $asset) {
        abort(404);
    }

    $path = public_path('images/og/'.$safeName);
    if (! is_file($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('asset', '[a-zA-Z0-9\-\.]+');

Route::get('/favicon/{asset}', function (string $asset) {
    $safeName = basename($asset);
    if ($safeName !== $asset) {
        abort(404);
    }

    $path = public_path('favicon/'.$safeName);
    if (! is_file($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('asset', '[a-zA-Z0-9\-\.]+');

Route::view('/{any?}', 'app')->where('any', '.*');
