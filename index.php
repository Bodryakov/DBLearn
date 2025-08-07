<?php
// index.php: Главная точка входа приложения

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'config.php';

// Старт сессии в самом начале
session_start(); 


// Check if the user is authenticated
if (empty($_SESSION['authenticated'])) {
    header('Location: /login');
    exit;
}

// Redirect to the admin panel
header('Location: /panel');
exit;

// После session_start() и перед обработкой маршрутов
// $request_uri = $_SERVER['REQUEST_URI'];
// $base_path = parse_url($request_uri, PHP_URL_PATH);
// $segments = $base_path ? explode('/', trim($base_path, '/')) : [];
// $segments = array_map('strtolower', $segments);




// Определение текущего URL
// $request_uri = $_SERVER['REQUEST_URI'];
// $base_path = parse_url($request_uri, PHP_URL_PATH);
// $segments = explode('/', trim($base_path, '/'));
// $segments = array_map('strtolower', $segments);

// Обработка маршрута /login
if (!empty($segments[0]) && $segments[0] === 'login') {
    require 'login.php';
    exit;
}

// Обработка выхода из системы
if (!empty($segments[0]) && $segments[0] === 'logout') {
    require 'logout.php';
    exit;
}



// Обработка остальных маршрутов
try {
    // Главная страница (список уровней)
    if (empty($segments[0])) {
        $stmt = $pdo->query("SELECT * FROM levels");
        $levels = $stmt->fetchAll();
        include 'templates/home.php';
        exit;
    }

    // Страница уровня
    if (count($segments) === 1) {
        $levelSlug = $segments[0];
        $stmt = $pdo->prepare("SELECT * FROM levels WHERE slug = ?");
        $stmt->execute([$levelSlug]);
        $level = $stmt->fetch();

        if (!$level) {
            http_response_code(404);
            exit("Уровень не найден");
        }

        $stmt = $pdo->prepare("SELECT * FROM sections WHERE level_id = ? ORDER BY order_num");
        $stmt->execute([$level['id']]);
        $sections = $stmt->fetchAll();

        include 'templates/level.php';
        exit;
    }

    // Страница раздела
    if (count($segments) === 2) {
        $levelSlug = $segments[0];
        $sectionSlug = $segments[1];

        // Проверка уровня
        $stmt = $pdo->prepare("SELECT * FROM levels WHERE slug = ?");
        $stmt->execute([$levelSlug]);
        $level = $stmt->fetch();

        if (!$level) {
            http_response_code(404);
            exit("Уровень не найден");
        }

        // Проверка раздела
        $stmt = $pdo->prepare("SELECT * FROM sections WHERE slug = ? AND level_id = ?");
        $stmt->execute([$sectionSlug, $level['id']]);
        $section = $stmt->fetch();

        if (!$section) {
            http_response_code(404);
            exit("Раздел не найден");
        }

        // Получение уроков
        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE section_id = ? ORDER BY order_num");
        $stmt->execute([$section['id']]);
        $lessons = $stmt->fetchAll();

        include 'templates/section.php';
        exit;
    }

    // Страница урока
    if (count($segments) === 3) {
        $levelSlug = $segments[0];
        $sectionSlug = $segments[1];
        $lessonSlug = $segments[2];

        // Проверка уровня
        $stmt = $pdo->prepare("SELECT * FROM levels WHERE slug = ?");
        $stmt->execute([$levelSlug]);
        $level = $stmt->fetch();

        if (!$level) {
            http_response_code(404);
            exit("Уровень не найден");
        }

        // Проверка раздела
        $stmt = $pdo->prepare("SELECT * FROM sections WHERE slug = ? AND level_id = ?");
        $stmt->execute([$sectionSlug, $level['id']]);
        $section = $stmt->fetch();

        if (!$section) {
            http_response_code(404);
            exit("Раздел не найден");
        }

        // Проверка урока
        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE slug = ? AND section_id = ?");
        $stmt->execute([$lessonSlug, $section['id']]);
        $lesson = $stmt->fetch();

        if (!$lesson) {
            http_response_code(404);
            exit("Урок не найден");
        }

        // Декодирование контента
        $content = json_decode($lesson['content'], true);
        include 'templates/lesson.php';
        exit;
    }

    // Если ничего не найдено - 404
    http_response_code(404);
    echo "Страница не найдена";
    exit;
    
} catch (PDOException $e) {
    http_response_code(500);
    echo "Ошибка базы данных: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo "Ошибка сервера: " . $e->getMessage();
    exit;
}

