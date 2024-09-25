<?php

use App\Services\CryptoPriceService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\FetchCryptoPrices;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

//Artisan::command('fetch:crypto-prices', function () {
//    $this->call(FetchCryptoPrices::class);
//})->describe('Fetch crypto prices in real-time using WebSocket');

Artisan::command('fetch:crypto-prices', function () {
    $this->call(FetchCryptoPrices::class);
})->describe('Fetch crypto prices in real-time using WebSocket');
