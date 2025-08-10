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
    // Разрешаем span, font, b, u, s, br, div, а также основные теги TinyMCE
    $allowedTags = '<p><h1><h2><h3><h4><h5><h6><ul><ol><li><code><pre><strong><em><a><table><tr><td><th><blockquote><span><font><b><u><s><br><div>';
    $html = strip_tags($html, $allowedTags);

    // Разрешаем только безопасные атрибуты: style, align, face, size, color, href, target
    $html = preg_replace_callback('/<(\w+)([^>]*)>/i', function($matches) {
        $tag = $matches[1];
        $attrs = $matches[2];
        // Разрешённые атрибуты
        $allowed = ['style', 'align', 'face', 'size', 'color', 'href', 'target', 'class'];
        preg_match_all('/(\w+)=("[^"]*"|\'[^\']*\')/i', $attrs, $attrMatches, PREG_SET_ORDER);
        $safeAttrs = '';
        foreach ($attrMatches as $attr) {
            if (in_array(strtolower($attr[1]), $allowed)) {
                $safeAttrs .= ' ' . $attr[1] . '=' . $attr[2];
            }
        }
        return '<' . $tag . $safeAttrs . '>';
    }, $html);
    return $html;
}