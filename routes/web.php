<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoWebSocketController;

Route::get('/test-websocket', [CryptoWebSocketController::class, 'testWebSocket']);

Route::get('/', function () {
    return view('welcome');
});
