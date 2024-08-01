<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function getMessage(User $friend)
    {
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
    }

    public function sendMessage(User $friend, Request $request)
    {
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
    }

    public function showChat(User $friend)
    {
        return view('chat', [
            'friend' => $friend
        ]);
    }

    public function index()
    {
        return view('wiadomosci', [
            'users' => User::whereNot('id', auth()->id())->get()
        ]); 
    }
}
