<?php
// api/get_lesson_content.php: API для получения контента урока
// Используется админ-панелью для загрузки содержимого при редактировании

require_once __DIR__ . '/../config.php';

if (isset($_GET['id'])) {
    $lessonId = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT content FROM lessons WHERE id = ?");
        $stmt->execute([$lessonId]);
        $lesson = $stmt->fetch();
        
        if ($lesson) {
            header('Content-Type: application/json');
            echo json_encode(['content' => $lesson['content']]);
            exit;
        }
    } catch (PDOException $e) {
        // Обработка ошибки
    }
}

http_response_code(404);
echo json_encode(['error' => 'Урок не найден']);