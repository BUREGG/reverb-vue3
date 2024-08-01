<?php

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'users' => User::whereNot('id', auth()->id())->get()
    ]);
})->middleware(['auth'])->name('dashboard');

Route::get('/chat/{friend}', function (User $friend) {
    return view('chat', [
        'friend' => $friend
    ]);
})->middleware(['auth'])->name('chat');

Route::get('/messages/{friend}', function (User $friend) {
    $userId = auth()->id();
    
    return Message::query()
        ->where(function ($query) use ($userId, $friend) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $friend->id);
        })
        ->orWhere(function ($query) use ($userId, $friend) {
            $query->where('sender_id', $friend->id)
                  ->where('receiver_id', $userId);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('id', 'asc')
        ->get();
})->middleware(['auth']);

Route::post('/messages/{friend}', function (Request $request, User $friend) {
    $validatedData = $request->validate([
        'message' => 'required|string|max:255',
    ]);

    $message = Message::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $friend->id,
        'text' => $validatedData['message']
    ]);
    broadcast(new MessageSent($message));

    return  $message;
});

require __DIR__ . '/auth.php';