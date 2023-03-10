<?php

$db = [
    'host' => 'localhost',
    'user' => 'vister4y_transl',
    'pass' => 'fM%2IteR',
    'db' => 'vister4y_transl'
];

$dsn = "mysql:host={$db['host']};dbname={$db['db']};charset=utf8";
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $db['user'], $db['pass'], $opt);

function get_categories($type = false)
{
    global $pdo;
    if (false == $type) {
        $stmt = $pdo->prepare("SELECT * FROM finance_cats");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM finance_cats WHERE type = ?");
        $stmt->execute([$type]);
    }
    $html = '';
    foreach ($stmt->fetchAll() as $item) {
        $html .= $item['title'] . PHP_EOL;
    }
    return $html;
}

function add_finance($type, $amount, $category)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM finance_cats WHERE type = ? AND title = ?");
    $stmt->execute([$type, $category]);
    $data = $stmt->fetch();

    if (!isset($data['id'])) {
        return "Категория не найдена";
    }

    $stmt = $pdo->prepare("INSERT INTO finance (amount, category, type) VALUES (?, ?, ?)");
    if ($stmt->execute([$amount, $data['id'], $type])) {
        return 'Запись добавлена';
    } else {
        return 'Ошибка добавления записи';
    }
}

function get_finance_today($type)
{
    global $pdo;

    if ($type === 1) {
        $html = "<u><b>Доходы за сегодня</b></u>" . PHP_EOL;
    } elseif ($type === 0) {
        $html = "<u><b>Расходы за сегодня</b></u>" . PHP_EOL;
    } else {
        $html = "<u><b>Итого за сегодня</b></u>" . PHP_EOL;
    }

    if (false === $type) {
        $stmt = $pdo->prepare("SELECT SUM(amount) as amount, type FROM finance WHERE DATE(date_add) = DATE(?) GROUP BY type ORDER BY type DESC;");
        $stmt->execute([date('Y-m-d')]);
        foreach ($stmt->fetchAll() as $item) {
            if ($item['type']) {
                $plus = $item['amount'];
            } else {
                $minus = $item['amount'];
            }
        }
        $plus = $plus ?? 0;
        $minus = $minus ?? 0;
        $html .= $plus - $minus;
    } else {
        $stmt = $pdo->prepare("SELECT SUM(f.amount) as amount, f.type, fc.title FROM finance f LEFT JOIN finance_cats fc ON f.category = fc.id WHERE f.type = ? AND DATE(date_add) = DATE(?) GROUP BY fc.title;");
        $stmt->execute([$type, date('Y-m-d')]);
        foreach ($stmt->fetchAll() as $item) {
            $html .= "{$item['title']}: {$item['amount']}" . PHP_EOL;
        }
    }
    return $html;
}