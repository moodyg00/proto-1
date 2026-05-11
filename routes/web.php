<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::any('/operations/work-orders/{any?}', function (): RedirectResponse {
    return redirect('/admin/jobs?view_type=table');
})->where('any', '.*');

Route::fallback(function () {
    abort(404);
});

