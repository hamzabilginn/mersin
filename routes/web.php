<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AIController;
use App\Models\User;

// Page Route - Single Page Application entry
Route::get('/', function () {
    return view('index');
});

// API Routes
Route::prefix('api')->group(function () {
    
    // User Switcher list
    Route::get('/users', function () {
        return response()->json(User::all());
    });

    // Task workflow routes
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::get('/bottlenecks', [TaskController::class, 'bottlenecks']);

    // Messaging routes
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Chatbot AI route
    Route::post('/chat', [AIController::class, 'chat']);
    Route::get('/ai-messages', [AIController::class, 'getMessages']);
    Route::post('/ai-messages', [AIController::class, 'storeMessage']);
});
