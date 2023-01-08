<?php



use Telegram\Bot\Api;


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/keyboards.php';

$token = '5512392790:AAGLi2milmn_fypDrfAnAPH8LhSsdnTVvUY';

//https://api.openweathermap.org/data/2.5/weather?appid=d14cf428d6476aeb2758a94bc7357fbc&units=metric&lang=ru&q=London

$telegram = new Api($token);

$update = $telegram->getWebhookUpdates();


file_put_contents(__DIR__ . '/logs.txt', print_r($update, 1), FILE_APPEND);

$chat_id = $update['message']['chat']['id'];
$text = $update['message']['text'];

if ($text == '/start') {
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Привет {$update['message']['chat']['first_name']}! Я бот, помогающий вести личную бухгатерию. Для получения справки есть команда /help",
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
} elseif ($text == '/help' || $text == 'Help') {
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Для ведения учета просто добавьте свой доход или расход в следующем формате:
        <b>Тип: сумма - категория</b>
        <u>Примеры комманд</u>
        Доход: 1000 - Зарплата
        Расход: 1000 - Коммунальные услуги",
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
} elseif ($text == 'Категории доходов' || $text == 'Help') {
    $data = get_categories(1);
    $answer = '<u>Категории доходов</u>' . PHP_EOL . $data;
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $answer,
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
} elseif ($text == 'Категории расходов' || $text == 'Help') {
    $data = get_categories(0);
    $answer = '<u>Категории расходов</u>' . PHP_EOL . $data;
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $answer,
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
} elseif (preg_match("#^Доход: (\d+) - ([\w ]+)#u", $text, $matches)) {
    $res = add_finance(1,$matches[1], $matches[2] );
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $res,
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
} elseif (preg_match("#^Расход: (\d+) - ([\w ]+)#u", $text, $matches)) {
    $res = add_finance(0, $matches[1], $matches[2]);
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $res,
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
} elseif ($text == 'Итого за сегодня') {
    $res = get_finance_today(false);
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $res,
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
} elseif ($text == 'Доходы за сегодня') {
    $res = get_finance_today(1);
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $res,
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
} elseif ($text == 'Расходы за сегодня') {
    $res = get_finance_today(0);
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $res,
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'keyboard' => $start_keyboard,
            'resize_keyboard' => true,
        ])
    ]);
}