<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ccxt\okex;
use React\EventLoop\Factory;
use Ratchet\Client\Connector;

class CryptoWebSocketController extends Controller
{
    public function testWebSocket()
    {
        // Инициализируем CCXT с API ключами
        $exchange = new okex([
            'apiKey'    => env('OKX_API_KEY'),
            'secret'    => env('OKX_API_SECRET'),
            'password'  => env('OKX_PASSWORD'),
        ]);

        try {
            // URL для WebSocket соединения
            $url = 'wss://ws.okx.com:8443/ws/v5/public';

            // Создаем цикл событий для WebSocket
            $loop = Factory::create();
            $connector = new Connector($loop);

            $connector($url)->then(function ($conn) use ($loop) {
                // Формируем правильный запрос для подписки
                $conn->send(json_encode([
                    'op' => 'subscribe',
                    'args' => [
                        [
                            'channel' => 'tickers',
                            'instId' => 'BTC-USDT'  // Подписываемся на тикер BTC-USDT
                        ]
                    ],
                ]));

                // Обрабатываем входящие сообщения
                $conn->on('message', function ($msg) {
                    echo "Received: {$msg}\n"; // Выводим полученное сообщение
                });

                // Закрываем соединение через 10 секунд
                $loop->addTimer(10, function () use ($conn) {
                    $conn->close();
                });

            }, function ($e) {
                echo "Could not connect: {$e->getMessage()}\n";
            });

            // Запускаем цикл событий
            $loop->run();

            return response()->json(['status' => 'WebSocket Test Completed']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
