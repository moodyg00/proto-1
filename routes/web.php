<?php

use App\Http\Controllers\PublicBookingController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/my-tasks');

Route::get('/booking/{token}', [PublicBookingController::class, 'show'])->name('public-booking.show');
Route::post('/booking/{token}', [PublicBookingController::class, 'store'])->name('public-booking.store');

Route::any('/operations/work-orders/{any?}', function (): RedirectResponse {
    return redirect('/admin/jobs?view_type=table');
})->where('any', '.*');

Route::fallback(function () {
    abort(404);
});

