<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<?php
// Файл panel.php
// Полная админ-панель с редактором уроков, тестов и задач

require_once __DIR__ . '/config.php';

// Загрузка данных для шаблонов
function loadData() {
    global $pdo;
    
    // Получение списка уровней
    $stmt = $pdo->query("SELECT * FROM levels");
    $levels = $stmt->fetchAll();
    
    // Получение списка разделов с названиями уровней
    $stmt = $pdo->query("SELECT s.*, l.title_ru as level_title 
                         FROM sections s 
                         JOIN levels l ON s.level_id = l.id 
                         ORDER BY s.order_num");
    $sections = $stmt->fetchAll();
    
    // Получение списка уроков с названиями разделов
    $stmt = $pdo->query("SELECT les.*, s.title_ru as section_title 
                         FROM lessons les 
                         JOIN sections s ON les.section_id = s.id 
                         ORDER BY les.order_num");
    $lessons = $stmt->fetchAll();
    
    return [$levels, $sections, $lessons];
}

// Проверка сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['authenticated'])) {
    header('Location: /login');
    exit;
}

// Загрузка данных
[$levels, $sections, $lessons] = loadData();

// Обработка POST-запросов (CRUD операции)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Добавление нового раздела
    if (isset($_POST['add_section'])) {
        $levelId = (int)$_POST['level_id'];
        $titleRu = trim($_POST['title_ru']);
        $slug = validateSlug(trim($_POST['slug']));
        $orderNum = (int)$_POST['order_num'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO sections (level_id, title_ru, slug, order_num) 
                                   VALUES (?, ?, ?, ?)");
            $stmt->execute([$levelId, $titleRu, $slug, $orderNum]);
            $success = "Раздел успешно добавлен!";
        } catch (PDOException $e) {
            $error = "Ошибка при добавлении раздела: " . $e->getMessage();
        }
    }
    
    // Обновление существующего раздела
    if (isset($_POST['update_section'])) {
        $sectionId = (int)$_POST['section_id'];
        $titleRu = trim($_POST['title_ru']);
        $slug = validateSlug(trim($_POST['slug']));
        $orderNum = (int)$_POST['order_num'];
        
        try {
            $stmt = $pdo->prepare("UPDATE sections 
                                   SET title_ru = ?, slug = ?, order_num = ? 
                                   WHERE id = ?");
            $stmt->execute([$titleRu, $slug, $orderNum, $sectionId]);
            $success = "Раздел успешно обновлён!";
        } catch (PDOException $e) {
            $error = "Ошибка при обновлении раздела: " . $e->getMessage();
        }
    }
    
    // Удаление раздела
    if (isset($_POST['delete_section'])) {
        $sectionId = (int)$_POST['section_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ?");
            $stmt->execute([$sectionId]);
            $success = "Раздел успешно удалён!";
        } catch (PDOException $e) {
            $error = "Ошибка при удалении раздела: " . $e->getMessage();
        }
    }
    
    // Добавление нового урока
    if (isset($_POST['add_lesson'])) {
        $sectionId = (int)$_POST['section_id'];
        $titleRu = trim($_POST['title_ru']);
        $slug = validateSlug(trim($_POST['slug']));
        $orderNum = (int)$_POST['order_num'];
        
        // Обработка тестов и задач
        $tests = $_POST['tests'] ?? [];
        $tasks = $_POST['tasks'] ?? [];
        
        // Формирование JSON структуры урока
        $lessonData = [
            'theory' => sanitizeHtml($_POST['content']),
            'tests' => [],
            'tasks' => []
        ];
        
        // Обработка тестов
        foreach ($tests as $test) {
            if (!empty($test['question']) && !empty($test['answers'])) {
                $answers = array_map('sanitizeHtml', $test['answers']);
                $lessonData['tests'][] = [
                    'question' => sanitizeHtml($test['question']),
                    'answers' => $answers,
                    'correct_index' => (int)($test['correct_index'] ?? 0)
                ];
            }
        }
        
        // Обработка задач
        foreach ($tasks as $task) {
            if (!empty($task['title'])) {
                $lessonData['tasks'][] = [
                    'title' => sanitizeHtml($task['title']),
                    'description' => sanitizeHtml($task['description'] ?? '')
                ];
            }
        }
        
        $contentJson = json_encode($lessonData, JSON_UNESCAPED_UNICODE);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO lessons (section_id, title_ru, slug, content, order_num) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sectionId, $titleRu, $slug, $contentJson, $orderNum]);
            $success = "Урок успешно добавлен!";
        } catch (PDOException $e) {
            $error = "Ошибка при добавлении урока: " . $e->getMessage();
        }
    }
    
    // Обновление существующего урока
    if (isset($_POST['update_lesson'])) {
        $lessonId = (int)$_POST['lesson_id'];
        $titleRu = trim($_POST['title_ru']);
        $slug = validateSlug(trim($_POST['slug']));
        $orderNum = (int)$_POST['order_num'];
        
        // Обработка тестов и задач
        $tests = $_POST['tests'] ?? [];
        $tasks = $_POST['tasks'] ?? [];
        
        // Формирование JSON структуры урока
        $lessonData = [
            'theory' => sanitizeHtml($_POST['content']),
            'tests' => [],
            'tasks' => []
        ];
        
        // Обработка тестов
        foreach ($tests as $test) {
            if (!empty($test['question']) && !empty($test['answers'])) {
                $answers = array_map('sanitizeHtml', $test['answers']);
                $lessonData['tests'][] = [
                    'question' => sanitizeHtml($test['question']),
                    'answers' => $answers,
                    'correct_index' => (int)($test['correct_index'] ?? 0)
                ];
            }
        }
        
        // Обработка задач
        foreach ($tasks as $task) {
            if (!empty($task['title'])) {
                $lessonData['tasks'][] = [
                    'title' => sanitizeHtml($task['title']),
                    'description' => sanitizeHtml($task['description'] ?? '')
                ];
            }
        }
        
        $contentJson = json_encode($lessonData, JSON_UNESCAPED_UNICODE);
        
        try {
            $stmt = $pdo->prepare("UPDATE lessons 
                                   SET title_ru = ?, slug = ?, content = ?, order_num = ? 
                                   WHERE id = ?");
            $stmt->execute([$titleRu, $slug, $contentJson, $orderNum, $lessonId]);
            $success = "Урок успешно обновлён!";
        } catch (PDOException $e) {
            $error = "Ошибка при обновлении урока: " . $e->getMessage();
        }
    }
    
    // Удаление урока
    if (isset($_POST['delete_lesson'])) {
        $lessonId = (int)$_POST['lesson_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
            $stmt->execute([$lessonId]);
            $success = "Урок успешно удалён!";
        } catch (PDOException $e) {
            $error = "Ошибка при удалении урока: " . $e->getMessage();
        }
    }
}

// Функция для генерации редактора тестов
function renderTestsEditor($tests) {
    $html = '<div class="tests-editor">';
    $html .= '<h3>Тесты</h3>';
    $html .= '<button type="button" class="btn add-test">+ Добавить вопрос</button>';
    $html .= '<div class="tests-container">';
    
    if (empty($tests)) {
        $html .= '<div class="test-item empty">Тесты не добавлены</div>';
    } else {
        foreach ($tests as $index => $test) {
            $html .= '<div class="test-item" data-index="'.$index.'">';
            $html .= '<div class="form-group">';
            $html .= '<label>Вопрос</label>';
            $html .= '<textarea name="tests['.$index.'][question]" class="form-control test-question">'.htmlspecialchars($test['question']).'</textarea>';
            $html .= '</div>';
            
            $html .= '<div class="answers-container">';
            foreach ($test['answers'] as $ansIndex => $answer) {
                $html .= '<div class="answer-item">';
                $html .= '<label>Вариант '.($ansIndex+1).'</label>';
                $html .= '<div class="answer-input">';
                $html .= '<input type="text" name="tests['.$index.'][answers][]" class="form-control" value="'.htmlspecialchars($answer).'">';
                $html .= '<input type="radio" name="tests['.$index.'][correct_index]" value="'.$ansIndex.'" '.($test['correct_index'] == $ansIndex ? 'checked' : '').'> Правильный';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            
            $html .= '<button type="button" class="btn btn-danger remove-test">Удалить вопрос</button>';
            $html .= '</div>';
        }
    }
    
    $html .= '</div></div>';
    return $html;
}

// Функция для генерации редактора задач
function renderTasksEditor($tasks) {
    $html = '<div class="tasks-editor">';
    $html .= '<h3>Практические задания</h3>';
    $html .= '<button type="button" class="btn add-task">+ Добавить задание</button>';
    $html .= '<div class="tasks-container">';
    
    if (empty($tasks)) {
        $html .= '<div class="task-item empty">Задания не добавлены</div>';
    } else {
        foreach ($tasks as $index => $task) {
            $html .= '<div class="task-item" data-index="'.$index.'">';
            $html .= '<div class="form-group">';
            $html .= '<label>Название задания</label>';
            $html .= '<input type="text" name="tasks['.$index.'][title]" class="form-control" value="'.htmlspecialchars($task['title']).'">';
            $html .= '</div>';
            
            $html .= '<div class="form-group">';
            $html .= '<label>Описание задания</label>';
            $html .= '<textarea name="tasks['.$index.'][description]" class="form-control">'.htmlspecialchars($task['description']).'</textarea>';
            $html .= '</div>';
            
            $html .= '<button type="button" class="btn btn-danger remove-task">Удалить задание</button>';
            $html .= '</div>';
        }
    }
    
    $html .= '</div></div>';
    return $html;
}

// Получение данных для отображения
try {
    // Получение всех уровней
    $levelsStmt = $pdo->query("SELECT * FROM levels");
    $levels = $levelsStmt->fetchAll();
    
    // Получение всех разделов с привязкой к уровням
    $sectionsStmt = $pdo->query("
        SELECT s.*, l.title_ru as level_title 
        FROM sections s 
        JOIN levels l ON s.level_id = l.id 
        ORDER BY l.id, s.order_num
    ");
    $sections = $sectionsStmt->fetchAll();
    
    // Получение всех уроков с привязкой к разделам
    $lessonsStmt = $pdo->query("
        SELECT l.*, s.title_ru as section_title, s.level_id
        FROM lessons l 
        JOIN sections s ON l.section_id = s.id 
        ORDER BY s.level_id, s.order_num, l.order_num
    ");
    $lessons = $lessonsStmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка при получении данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель DOMLearn</title>
    <link rel="stylesheet" href="/css/admin.css">
    <script src="/tinymce/tinymce.min.js"></script>
    <style>
        /* Специфические стили для админ-панели */
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .admin-section {
            margin-bottom: 40px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .admin-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn {
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2196f3;
        }
        
        .btn-danger {
            background: #f44336;
        }
        
        .btn-danger:hover {
            background: #e53935;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        .data-table tr:hover {
            background: #f9f9f9;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success {
            background: #e8f5e9;
            color: #4caf50;
            border: 1px solid #c8e6c9;
        }
        
        .error {
            background: #ffebee;
            color: #f44336;
            border: 1px solid #ffcdd2;
        }
        
        /* Стили для вкладок */
        .lesson-tabs {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab-btn {
            padding: 10px 20px;
            background: #f5f5f5;
            border: none;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .tab-btn.active {
            background: #1976d2;
            color: white;
        }
        
        .tab-pane {
            display: none;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 0 0 4px 4px;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        /* Стили для редактора тестов и заданий */
        .tests-editor, .tasks-editor {
            margin-top: 20px;
        }
        
        .test-item, .task-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            background: #fafafa;
        }
        
        .answers-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
        }
        
        .answer-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }
        
        .answer-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .answer-input input[type="text"] {
            flex-grow: 1;
        }
        
        /* Кнопки удаления */
        .remove-test, .remove-task {
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div style="text-align: right; padding: 10px;">
      <a href="/logout" class="btn btn-danger">Выйти</a>
    </div>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Админ-панель DOMLearn</h1>
            <p>Управление разделами и уроками</p>
        </div>

        <!-- Вывод сообщений об ошибке -->
        <?php if (isset($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Секция управления разделами -->
        <div class="admin-section">
            <h2>Управление разделами</h2>
            
            <!-- Форма добавления/редактирования раздела -->
            <form method="POST" class="admin-form">
                <input type="hidden" name="section_id" id="section_id" value="">
                
                <div class="form-group">
                    <label for="level_id">Уровень</label>
                    <select name="level_id" id="level_id" class="form-control" required>
                        <?php foreach ($levels as $level): ?>
                            <option value="<?= $level['id'] ?>"><?= htmlspecialchars($level['title_ru']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="title_ru">Название раздела (RU)</label>
                    <input type="text" name="title_ru" id="title_ru" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug (англ., только a-z, 0-9, дефисы)</label>
                    <input type="text" name="slug" id="slug" class="form-control" required maxlength="30">
                </div>
                
                <div class="form-group">
                    <label for="order_num">Порядковый номер</label>
                    <input type="number" name="order_num" id="order_num" class="form-control" min="0" value="0" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="add_section" class="btn">Добавить раздел</button>
                    <button type="submit" name="update_section" class="btn" style="display:none">Обновить раздел</button>
                    <button type="button" id="cancelEdit" class="btn" style="display:none">Отмена</button>
                </div>
                <?php if (isset($success) && strpos($success, 'Раздел успешно добавлен') !== false): ?>
                    <div class="message success" style="grid-column: 1 / -1; margin-top: 0.5rem;"> <?= htmlspecialchars($success) ?> </div>
                <?php endif; ?>
            </form>

            <!-- Таблица существующих разделов -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Уровень</th>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Порядок</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $section): ?>
                        <tr>
                            <td><?= $section['id'] ?></td>
                            <td><?= htmlspecialchars($section['level_title']) ?></td>
                            <td><?= htmlspecialchars($section['title_ru']) ?></td>
                            <td><?= htmlspecialchars($section['slug']) ?></td>
                            <td><?= $section['order_num'] ?></td>
                            <td>
                                <button class="btn edit-section" 
                                        data-id="<?= $section['id'] ?>"
                                        data-level="<?= $section['level_id'] ?>"
                                        data-title="<?= htmlspecialchars($section['title_ru']) ?>"
                                        data-slug="<?= htmlspecialchars($section['slug']) ?>"
                                        data-order="<?= $section['order_num'] ?>">
                                    Изменить
                                </button>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
                                    <button type="submit" name="delete_section" class="btn btn-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Секция управления уроками -->
        <div class="admin-section">
            <h2>Управление уроками</h2>
            
            <!-- Форма добавления/редактирования урока -->
            <form method="POST" class="admin-form">
                <input type="hidden" name="lesson_id" id="lesson_id" value="">
                
                <div class="form-group">
                    <label for="section_id">Раздел</label>
                    <select name="section_id" id="section_id" class="form-control" required>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= $section['id'] ?>">
                                <?= htmlspecialchars($section['level_title']) ?> / 
                                <?= htmlspecialchars($section['title_ru']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="lesson_title_ru">Название урока (RU)</label>
                    <input type="text" name="title_ru" id="lesson_title_ru" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="lesson_slug">Slug (англ., только a-z, 0-9, дефисы)</label>
                    <input type="text" name="slug" id="lesson_slug" class="form-control" required maxlength="30">
                </div>
                
                <div class="form-group">
                    <label for="lesson_order_num">Порядковый номер</label>
                    <input type="number" name="order_num" id="lesson_order_num" class="form-control" min="0" value="0" required>
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <label for="content">Содержимое урока</label>
                    
                    <!-- Навигация по разделам урока -->
                    <div class="lesson-tabs">
                        <button type="button" class="tab-btn active" data-tab="theory">Теория</button>
                        <button type="button" class="tab-btn" data-tab="tests">Тесты</button>
                        <button type="button" class="tab-btn" data-tab="tasks">Задания</button>
                    </div>
                    
                    <!-- Содержимое вкладок -->
                    <div class="tab-content">
                        <!-- Вкладка теории -->
                        <div id="theory-tab" class="tab-pane active">
                            <textarea name="content" id="content" class="form-control" rows="20"></textarea>
                        </div>
                        
                        <!-- Вкладка тестов -->
                        <div id="tests-tab" class="tab-pane">
                            <?php
                            $tests = [];
                            if (isset($_POST['lesson_id']) && !empty($_POST['lesson_id'])) {
                                $lessonId = (int)$_POST['lesson_id'];
                                $stmt = $pdo->prepare("SELECT content FROM lessons WHERE id = ?");
                                $stmt->execute([$lessonId]);
                                $lesson = $stmt->fetch();
                                
                                if ($lesson) {
                                    $content = json_decode($lesson['content'], true);
                                    if (isset($content['tests'])) {
                                        $tests = $content['tests'];
                                    }
                                }
                            }
                            echo renderTestsEditor($tests);
                            ?>
                        </div>
                        
                        <!-- Вкладка заданий -->
                        <div id="tasks-tab" class="tab-pane">
                            <?php
                            $tasks = [];
                            if (isset($_POST['lesson_id']) && !empty($_POST['lesson_id'])) {
                                $lessonId = (int)$_POST['lesson_id'];
                                $stmt = $pdo->prepare("SELECT content FROM lessons WHERE id = ?");
                                $stmt->execute([$lessonId]);
                                $lesson = $stmt->fetch();
                                
                                if ($lesson) {
                                    $content = json_decode($lesson['content'], true);
                                    if (isset($content['tasks'])) {
                                        $tasks = $content['tasks'];
                                    }
                                }
                            }
                            echo renderTasksEditor($tasks);
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <button type="submit" name="add_lesson" class="btn">Добавить урок</button>
                    <button type="submit" name="update_lesson" class="btn" style="display:none">Обновить урок</button>
                    <button type="button" id="cancelLessonEdit" class="btn" style="display:none">Отмена</button>
                </div>
                <?php if (isset($success) && strpos($success, 'Урок успешно добавлен') !== false): ?>
                    <div class="message success" style="grid-column: 1 / -1; margin-top: 0.5rem;"> <?= htmlspecialchars($success) ?> </div>
                <?php endif; ?>
            </form>

            <!-- Таблица существующих уроков -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Уровень</th>
                        <th>Раздел</th>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Порядок</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lessons as $lesson): ?>
                        <tr>
                            <td><?= $lesson['id'] ?></td>
                            <td>
                                <?= $levels[array_search($lesson['level_id'], array_column($levels, 'id'))]['title_ru'] ?>
                            </td>
                            <td><?= htmlspecialchars($lesson['section_title']) ?></td>
                            <td><?= htmlspecialchars($lesson['title_ru']) ?></td>
                            <td><?= htmlspecialchars($lesson['slug']) ?></td>
                            <td><?= $lesson['order_num'] ?></td>
                            <td>
                                <button class="btn edit-lesson" 
                                        data-id="<?= $lesson['id'] ?>"
                                        data-section="<?= $lesson['section_id'] ?>"
                                        data-title="<?= htmlspecialchars($lesson['title_ru']) ?>"
                                        data-slug="<?= htmlspecialchars($lesson['slug']) ?>"
                                        data-order="<?= $lesson['order_num'] ?>">
                                    Изменить
                                </button>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                    <button type="submit" name="delete_lesson" class="btn btn-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Инициализация TinyMCE редактора
        tinymce.init({
            selector: '#content',
            license_key: 'gpl', // Используйте эту строку для GPL лицензии
            plugins: 'lists link table code',
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table | code',
            height: 500,
            license_key: 'gpl',
            content_css: '/css/style.css'
        });

        // JavaScript для админ-панели
        document.addEventListener('DOMContentLoaded', function() {
            // Редактирование раздела
            const editSectionBtns = document.querySelectorAll('.edit-section');
            editSectionBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('section_id').value = this.dataset.id;
                    document.getElementById('level_id').value = this.dataset.level;
                    document.getElementById('title_ru').value = this.dataset.title;
                    document.getElementById('slug').value = this.dataset.slug;
                    document.getElementById('order_num').value = this.dataset.order;
                    
                    document.querySelector('[name="add_section"]').style.display = 'none';
                    document.querySelector('[name="update_section"]').style.display = 'inline-block';
                    document.getElementById('cancelEdit').style.display = 'inline-block';
                });
            });
            
            // Отмена редактирования раздела
            document.getElementById('cancelEdit').addEventListener('click', function() {
                document.getElementById('section_id').value = '';
                document.getElementById('title_ru').value = '';
                document.getElementById('slug').value = '';
                document.getElementById('order_num').value = '0';
                
                document.querySelector('[name="add_section"]').style.display = 'inline-block';
                document.querySelector('[name="update_section"]').style.display = 'none';
                this.style.display = 'none';
            });
            
            // Редактирование урока
            const editLessonBtns = document.querySelectorAll('.edit-lesson');
            editLessonBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('lesson_id').value = this.dataset.id;
                    document.getElementById('section_id').value = this.dataset.section;
                    document.getElementById('lesson_title_ru').value = this.dataset.title;
                    document.getElementById('lesson_slug').value = this.dataset.slug;
                    document.getElementById('lesson_order_num').value = this.dataset.order;
                    
                    // Загрузка контента урока
                    fetch(`/api/get_lesson_content.php?id=${this.dataset.id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.content) {
                                const lessonData = JSON.parse(data.content);
                                tinymce.get('content').setContent(lessonData.theory || '');
                            }
                        })
                        .catch(error => console.error('Ошибка загрузки контента:', error));
                    
                    document.querySelector('[name="add_lesson"]').style.display = 'none';
                    document.querySelector('[name="update_lesson"]').style.display = 'inline-block';
                    document.getElementById('cancelLessonEdit').style.display = 'inline-block';
                });
            });
            
            // Отмена редактирования урока
            document.getElementById('cancelLessonEdit').addEventListener('click', function() {
                document.getElementById('lesson_id').value = '';
                document.getElementById('lesson_title_ru').value = '';
                document.getElementById('lesson_slug').value = '';
                document.getElementById('lesson_order_num').value = '0';
                tinymce.get('content').setContent('');
                
                document.querySelector('[name="add_lesson"]').style.display = 'inline-block';
                document.querySelector('[name="update_lesson"]').style.display = 'none';
                this.style.display = 'none';
            });
            
            // Переключение вкладок урока
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabName = this.dataset.tab;
                    
                    // Активируем кнопку
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Показываем соответствующую вкладку
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('active');
                    });
                    document.getElementById(`${tabName}-tab`).classList.add('active');
                });
            });
            
            // Добавление нового теста
            document.querySelector('.add-test')?.addEventListener('click', function() {
                const container = this.closest('.tests-editor').querySelector('.tests-container');
                const emptyMsg = container.querySelector('.empty');
                if (emptyMsg) emptyMsg.remove();
                
                const index = container.querySelectorAll('.test-item').length;
                const testHtml = `
                    <div class="test-item" data-index="${index}">
                        <div class="form-group">
                            <label>Вопрос</label>
                            <textarea name="tests[${index}][question]" class="form-control test-question"></textarea>
                        </div>
                        <div class="answers-container">
                            ${Array.from({length: 4}, (_, i) => `
                                <div class="answer-item">
                                    <label>Вариант ${i+1}</label>
                                    <div class="answer-input">
                                        <input type="text" name="tests[${index}][answers][]" class="form-control">
                                        <input type="radio" name="tests[${index}][correct_index]" value="${i}">
                                        Правильный
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <button type="button" class="btn btn-danger remove-test">Удалить вопрос</button>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', testHtml);
            });
            
            // Удаление теста
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-test')) {
                    e.target.closest('.test-item').remove();
                    // Обновить индексы оставшихся тестов
                    document.querySelectorAll('.tests-container .test-item').forEach((item, index) => {
                        item.dataset.index = index;
                        item.querySelectorAll('[name]').forEach(el => {
                            const name = el.name.replace(/tests\[\d+\]/, `tests[${index}]`);
                            el.name = name;
                        });
                    });
                }
            });
            
            // Добавление нового задания
            document.querySelector('.add-task')?.addEventListener('click', function() {
                const container = this.closest('.tasks-editor').querySelector('.tasks-container');
                const emptyMsg = container.querySelector('.empty');
                if (emptyMsg) emptyMsg.remove();
                
                const index = container.querySelectorAll('.task-item').length;
                const taskHtml = `
                    <div class="task-item" data-index="${index}">
                        <div class="form-group">
                            <label>Название задания</label>
                            <input type="text" name="tasks[${index}][title]" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Описание задания</label>
                            <textarea name="tasks[${index}][description]" class="form-control"></textarea>
                        </div>
                        <button type="button" class="btn btn-danger remove-task">Удалить задание</button>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', taskHtml);
            });
            
            // Удаление задания
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-task')) {
                    e.target.closest('.task-item').remove();
                    // Обновить индексы оставшихся заданий
                    document.querySelectorAll('.tasks-container .task-item').forEach((item, index) => {
                        item.dataset.index = index;
                        item.querySelectorAll('[name]').forEach(el => {
                            const name = el.name.replace(/tasks\[\d+\]/, `tasks[${index}]`);
                            el.name = name;
                        });
                    });
                }
            });
        });
    </script>
</body>
</html>