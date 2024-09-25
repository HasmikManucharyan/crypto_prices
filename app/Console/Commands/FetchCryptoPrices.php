<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CryptoPriceService;

class FetchCryptoPrices extends Command
{
    protected $signature = 'fetch:crypto-prices';
    protected $description = 'Fetch crypto prices in real-time using WebSocket';

    protected $service;

    public function __construct(CryptoPriceService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $this->info('Connecting to WebSocket and subscribing to prices...');

        // Call the method from the service to connect and subscribe
        $pairs = 'BTC-USDT';
        $this->service->connectAndSubscribe();

        $this->info('WebSocket connection closed.');
    }
}
