<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($lesson['title_ru']) ?> | DOMLearn</title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- Highlight.js style -->
    <link rel="stylesheet" href="/highlightjs/styles/default.min.css">
    <script src="/js/app.js" defer></script>
    <!-- Highlight.js library -->
    <script src="/highlightjs/highlight.min.js" defer></script>
</head>
<body>
    <header class="app-header">
        <h1><?= htmlspecialchars($lesson['title_ru']) ?></h1>
        <div class="lesson-path">
            <?= htmlspecialchars($level['title_ru']) ?> / 
            <?= htmlspecialchars($section['title_ru']) ?>
        </div>
    </header>

    <main class="lesson-container">
        <!-- Теоретическая часть -->
        <section class="theory">
            <?= $content['theory'] ?>
        </section>
        
        <!-- Тесты -->
        <?php if (!empty($content['tests'])): ?>
            <section class="tests">
                <h2>Проверь себя</h2>
                <?php foreach ($content['tests'] as $index => $test): ?>
                    <div class="test-item" data-test-id="<?= $index ?>" data-correct-index="<?= $test['correct_index'] ?>">
                        <p class="test-question"><?= $test['question'] ?></p>
                        <div class="answers">
                            <?php foreach ($test['answers'] as $ansIndex => $answer): ?>
                                <div class="answer" data-answer-id="<?= $ansIndex ?>">
                                    <input type="radio" name="test_<?= $index ?>" id="ans_<?= $index ?>_<?= $ansIndex ?>">
                                    <label for="ans_<?= $index ?>_<?= $ansIndex ?>"><?= $answer ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        
        <!-- Задачи -->
        <?php if (!empty($content['tasks'])): ?>
            <section class="tasks">
                <h2>Практические задания</h2>
                <?php foreach ($content['tasks'] as $task): ?>
                    <div class="task-item">
                        <h3><?= htmlspecialchars($task['title']) ?></h3>
                        <p><?= $task['description'] ?></p>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        
        <!-- Навигация -->
        <div class="lesson-navigation">
            <?php
            // Запрос предыдущего урока
            $stmt = $pdo->prepare("SELECT * FROM lessons 
                                  WHERE section_id = ? AND order_num < ? 
                                  ORDER BY order_num DESC LIMIT 1");
            $stmt->execute([$section['id'], $lesson['order_num']]);
            $prevLesson = $stmt->fetch();
            
            // Запрос следующего урока
            $stmt = $pdo->prepare("SELECT * FROM lessons 
                                  WHERE section_id = ? AND order_num > ? 
                                  ORDER BY order_num ASC LIMIT 1");
            $stmt->execute([$section['id'], $lesson['order_num']]);
            $nextLesson = $stmt->fetch();
            ?>
            
            <?php if ($prevLesson): ?>
                <a href="/<?= $levelSlug ?>/<?= $sectionSlug ?>/<?= $prevLesson['slug'] ?>" class="nav-btn prev">
                    ← Предыдущий урок
                </a>
            <?php endif; ?>
            
            <a href="/" class="nav-btn home">В оглавление</a>
            
            <?php if ($nextLesson): ?>
                <a href="/<?= $levelSlug ?>/<?= $sectionSlug ?>/<?= $nextLesson['slug'] ?>" class="nav-btn next">
                    Следующий урок →
                </a>
            <?php endif; ?>
        </div>
    </main>
    
    <footer class="app-footer">
        <p>DOMLearn &copy; <?= date('Y') ?></p>
    </footer>
</body>
</html>