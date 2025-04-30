<?php
class SteamDealsMonitor
{
    private const STEAM_API_URL = 'https://store.steampowered.com/api/featuredcategories';
    private const MIN_DISCOUNT = 50; // Минимальный процент скидки
    private const CACHE_TIME = 3600; // Кэширование на 1 час

    private string $cacheDir;

    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/cache';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Получает список всех акционных товаров из Steam
     */
    public function fetchSteamDeals(): array
    {
        $cacheFile = $this->cacheDir . '/steam_deals.json';

        if (file_exists($cacheFile) && time() - filemtime($cacheFile) < self::CACHE_TIME) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        $response = file_get_contents(self::STEAM_API_URL);
        if (!$response) {
            throw new RuntimeException('Не удалось получить данные от Steam API');
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Ошибка декодирования JSON: ' . json_last_error_msg());
        }

        file_put_contents($cacheFile, json_encode($data));
        return $data;
    }

    /**
     * Фильтрует игры по размеру скидки
     */
    public function filterDiscountedGames(array $games, int $minDiscount = null): array
    {
        $minDiscount = $minDiscount ?? self::MIN_DISCOUNT;
        $result = [];

        foreach ($games['specials']['items'] ?? [] as $game) {
            $discount = $game['discount_percent'] ?? 0;
            if ($discount >= $minDiscount) {
                $result[] = $this->formatGameData($game);
            }
        }

        return $result;
    }

    /**
     * Форматирует данные игры
     */
    private function formatGameData(array $game): array
    {
        return [
            'id' => $game['id'],
            'title' => $game['name'],
            'original_price' => $game['original_price'] / 100,
            'discount_price' => $game['final_price'] / 100,
            'discount_percent' => $game['discount_percent'],
            'image' => "https://cdn.cloudflare.steamstatic.com/steam/apps/{$game['id']}/header.jpg",
            'url' => "https://store.steampowered.com/app/{$game['id']}",
            'rating' => $game['review_score'] ?? 0,
            'release_date' => $game['release_date']['date'] ?? 'Уже выпущена'
        ];
    }

    /**
     * Генерирует текстовый отчет
     */
    public function generateTextReport(array $games): string
    {
        if (empty($games)) {
            return "На данный момент нет игр со скидкой более " . self::MIN_DISCOUNT . "%";
        }

        $report = "🔥 Актуальные скидки в Steam (от " . self::MIN_DISCOUNT . "%):\n\n";

        foreach ($games as $game) {
            $report .= sprintf(
                "🎮 %s\n💰 %s → %s (-%d%%)\n⭐ Рейтинг: %d/100\n🖼️ %s\n🔗 %s\n\n",
                $game['title'],
                $game['original_price'],
                $game['discount_price'],
                $game['discount_percent'],
                $game['rating'],
                $game['image'],
                $game['url']
            );
        }

        return $report . "🔄 Обновлено: " . date('Y-m-d H:i');
    }


}