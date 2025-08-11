<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/start-chat', function(){ return view('StartChat'); })->name('chat.start.form');
// Route::post('/start-chat', [ChatController::class, 'startChatWeb'])->name('chat.start.web');
// Route::get('/chat/{chatId}', [ChatController::class, 'showChat'])->name('chat.view');
// Route::post('/chat/{chatId}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::view('/test-chat', 'StartChat');
