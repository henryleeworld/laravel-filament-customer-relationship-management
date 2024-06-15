<?php

use App\Http\Controllers\QuotePdfController;
use App\Livewire\AcceptInvitation;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('/admin');
});
Route::middleware(['signed'])->group(function () {
    Route::get('invitation/{invitation}/accept', AcceptInvitation::class)->name('invitation.accept');
    Route::get('quotes/{quote}/pdf', QuotePdfController::class)->name('quotes.pdf');
});
