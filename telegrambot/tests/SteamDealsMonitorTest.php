<?php
use PHPUnit\Framework\TestCase;

// Подключите ваш основной класс, если не используется автозагрузка
require_once __DIR__ . '/../src/SteamDealsMonitor.php';

class SteamDealsMonitorTest extends TestCase
{
    private SteamDealsMonitor $monitor;

    protected function setUp(): void
    {
        $this->monitor = new SteamDealsMonitor();
    }

    public function testFilterDiscountedGames()
    {
        // Тестовые данные в формате Steam API (цены в центах)
        $testData = [
            'specials' => [
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Game 1',
                        'original_price' => 2000, // $20.00
                        'final_price' => 1000,       // $10.00
                        'discount_percent' => 50,
                        'review_score' => 80
                    ],
                    [
                        'id' => 2,
                        'name' => 'Game 2',
                        'original_price' => 3000,    // $30.00
                        'final_price' => 2700,       // $27.00
                        'discount_percent' => 10,
                        'review_score' => 90
                    ]
                ]
            ]
        ];

        $result = $this->monitor->filterDiscountedGames($testData);

        // Должен пройти только Game 1 (скидка ≥50%)
        $this->assertCount(1, $result);
        $this->assertEquals('Game 1', $result[0]['title']);

    }


    public function testGenerateTextReport()
    {
        // Тестовый набор данных
        $games = [
            [
                'title' => 'Cyberpunk 2077',
                'original_price' => 59.99,
                'discount_price' => 29.99,
                'discount_percent' => 50,
                'rating' => 75,
                'url' => 'https://store.steampowered.com/app/1091500'
            ]
        ];

        $expectedReport = <<<TEXT
        Список игр со скидкой:
        Название: Cyberpunk 2077
        Цена: 59.99 → 29.99 (-50%)
        Рейтинг: 75
        Ссылка: https://store.steampowered.com/app/1091500
        
        TEXT;

        $report = $this->monitor->generateTextReport($games);
        $this->assertEquals($expectedReport, $report);
    }
}