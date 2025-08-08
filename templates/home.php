<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOMLearn - Изучение JavaScript DOM</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="app-header">
        <h1>DOMLearn</h1>
        <p>Интерактивное обучение работе с DOM в JavaScript</p>
    </header>
    
    <main class="levels-container">
        <?php foreach ($levels as $level): ?>
            <section class="level-card" data-level-slug="<?= htmlspecialchars($level['slug']) ?>">
                <h2><?= htmlspecialchars($level['title_ru']) ?></h2>
                <div class="sections-container">
                    <?php foreach ($level['sections'] as $section): ?>
                        <div class="section-item">
                            <h3><?= htmlspecialchars($section['title_ru']) ?></h3>
                            <p class="lessons-count">Уроков: <?= (int)$section['lessons_count'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="/<?= htmlspecialchars($level['slug']) ?>" class="level-link">Начать обучение</a>
            </section>
        <?php endforeach; ?>
    </main>
    
    <footer class="app-footer">
        <p>DOMLearn &copy; <?= date('Y') ?></p>
    </footer>
</body>
</html>