<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use React\EventLoop\Factory;
use Ratchet\Client\Connector;
use ccxt\okex;

class CryptoPriceService
{
    protected $exchange;

    public function __construct()
    {
        // Initialize CCXT with API keys
        $this->exchange = new okex([
            'apiKey'    => env('OKX_API_KEY'),
            'secret'    => env('OKX_API_SECRET'),
            'password'  => env('OKX_PASSWORD'),  // Include password if required
        ]);
    }

    public function connectAndSubscribe()
    {
        // Define the WebSocket URL
        $url = 'wss://ws.okx.com:8443/ws/v5/public';
        $loop = Factory::create();
        $connector = new Connector($loop);

        $connector($url)->then(function ($conn) use ($loop) {
            // Subscribe to the BTC-USDT ticker channel
            $pair = 'BTC-USDT';
            $conn->send(json_encode([
                'op' => 'subscribe',
                'args' => [
                    [
                        'channel' => 'tickers',
                        'instId' => $pair  // Subscribe to the BTC-USDT ticker
                    ],
                ],
            ]));

            // Handle incoming messages
            $conn->on('message', function ($msg) use ($pair) {
                $data = json_decode($msg, true);
                if (isset($data['data'][0])) {
                    $this->storePrice($data['data'][0]); // Store price data
                }
            });

            // Close the connection after 10 seconds
            $loop->addTimer(10, function () use ($conn) {
                $conn->close();
            });

        }, function ($e) {
            // Handle connection errors
            echo "Could not connect: {$e->getMessage()}\n";
        });

        // Run the event loop
        $loop->run();
    }
    public function storePrice($tickerData)
    {
        // Extract relevant data from the ticker response
        $priceData = [
            'pair' => $tickerData['instId'],
            'buy_price' => $tickerData['askPx'],
            'sell_price' => $tickerData['bidPx'],
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Save the data to the database
        try {
            DB::table('crypto_prices')->insert($priceData);
            Log::info("Stored price data for {$tickerData['instId']}: " . json_encode($priceData));
        } catch (\Exception $e) {
            Log::error("Error storing price data: " . $e->getMessage());
        }
    }
}
