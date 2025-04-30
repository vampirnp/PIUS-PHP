<?php
require 'vendor/autoload.php';
require 'src/SteamDealsMonitor.php';

try {
    $monitor = new SteamDealsMonitor();

    // Получаем все скидки
    $allDeals = $monitor->fetchSteamDeals();

    // Фильтруем по минимальной скидке
    $filteredGames = $monitor->filterDiscountedGames($allDeals);

    // Генерируем отчет
    $report = $monitor->generateTextReport($filteredGames);

    // Выводим результат
    echo $report;

} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}