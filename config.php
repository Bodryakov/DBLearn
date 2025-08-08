<?php
// config.php: Подключение к БД и базовые функции

// Загрузка переменных окружения
$env = parse_ini_file('.env');

// Параметры подключения к БД
$host = $env['DB_HOST'];
$dbname = $env['DB_NAME'];
$username = $env['DB_USER'];
$password = $env['DB_PASSWORD'];

// Создание подключения
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

/**
 * Генерация slug из русскоязычного заголовка
 * @param string $title Русскоязычный заголовок
 * @return string Slug (только a-z, 0-9, дефисы)
 */
function generateSlug($title) {
    // Транслитерация русских букв
    $translit = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    
    $slug = mb_strtolower($title, 'UTF-8');
    $slug = strtr($slug, $translit);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 30);
    
    return $slug;
}

/**
 * Проверка и очистка slug
 * @param string $slug Исходный slug
 * @return string Валидный slug
 */
function validateSlug($slug) {
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return substr($slug, 0, 30);
}

/**
 * Очистка HTML контента от опасных элементов
 * @param string $html Исходный HTML
 * @return string Безопасный HTML
 */
function sanitizeHtml($html) {
    $allowedTags = '<p><h1><h2><h3><h4><h5><h6><ul><ol><li><code><pre><strong><em><a><table><tr><td><th><blockquote>';
    $html = strip_tags($html, $allowedTags);

    // Удаление запрещённых атрибутов
    $html = preg_replace('/\s(on\w+)=\"[^\"]*\"|\'[^\']*\'/i', '', $html);
    $html = preg_replace('/\sstyle=("|\')[^"\']*("|\')/i', '', $html);
    // Удаляем class у всех тегов, кроме <code> и <pre>
    $html = preg_replace_callback('/<(?!code|pre)([a-z0-9]+)([^>]*)>/i', function($matches) {
        $tag = $matches[1];
        $attrs = preg_replace('/\sclass=("|\')[^"\']*("|\')/i', '', $matches[2]);
        return '<' . $tag . $attrs . '>';
    }, $html);
    return $html;
}