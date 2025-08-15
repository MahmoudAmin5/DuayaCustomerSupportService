<?php

use App\Http\Controllers\AgentDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatWebController;
use App\Http\Controllers\Agent\AgentAuthController;

// Route::middleware('agent')->group(function () {
//     Route::get('/agent/dashboard', [AgentDashboardController::class, 'index'])->name('agent.dashboard');
// });
Route::prefix('agent')->name('agent.')->group(function () {
    Route::get('/login', [AgentAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AgentAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AgentAuthController::class, 'logout'])->name('logout');

    Route::middleware(['agent'])->group(function () {
        Route::get('/dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');
    });
});
Route::get('customer/chat/{chatId}', [ChatWebController::class, 'showCustomerChat'])->name('customer.show');
Route::get('/chat/{chatId}', [ChatWebController::class, 'showAgentChat'])->name('chat.show');




Route::view('/startchat', 'StartChat');
Route::post('/chat/start', [ChatWebController::class, 'startChat'])->name('startChat');

Route::post('/chat/{chatId}/send', [ChatWebController::class, 'sendMessageAsAgent'])->name('chat.send');
Route::post('/customer/chat/{chatId}/send', [ChatWebController::class, 'sendMessageAsCustomer'])->name('customer.chat.send');
Route::post('/agent/chat/{chatId}/close', [ChatWebController::class, 'closeChat'])
    ->middleware('agent')
    ->name('agent.chat.close');
