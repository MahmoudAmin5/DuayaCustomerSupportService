<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatWebController;

Route::get('/', function () {
    return view('welcome');
});
Route::view('/start-chat', 'StartChat');
Route::post('/chat/start', [ChatWebController::class, 'startChat'])->name('startChat');
Route::get('/chat/{chatId}', [ChatWebController::class, 'showChat'])->name('chat.show');
Route::post('/chat/{chatId}/send', [ChatWebController::class, 'sendMessage'])->name('chat.send');
