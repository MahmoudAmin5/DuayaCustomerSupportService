<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});
Route::view('/start-chat', 'StartChat');
Route::get('/chat/{chatId}', [ChatController::class, 'showChat'])->name('chat.show');
