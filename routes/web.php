<?php

use App\Events\MessageSent;
use App\Http\Controllers\MessageController;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/wiadomosci', [MessageController::class, 'index'])->middleware(['auth'])->name('wiadomosci');

Route::get('/chat/{friend}', [MessageController::class, 'showChat'])->middleware(['auth'])->name('chat');

Route::get('/messages/{friend}', [MessageController::class, 'getMessage'])->middleware(['auth']);

Route::post('/messages/{friend}', [MessageController::class, 'sendMessage']);

require __DIR__ . '/auth.php';