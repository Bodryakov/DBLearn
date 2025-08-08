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
                            <a href="/<?= htmlspecialchars($level['slug']) ?>/<?= htmlspecialchars($section['slug']) ?>" class="section-link">
                                <h3><?= htmlspecialchars($section['title_ru']) ?></h3>
                            </a>
                            <?php if (!empty($section['lessons'])): ?>
                                <div class="section-lessons">
                                    <ul>
                                        <?php foreach ($section['lessons'] as $lesson): ?>
                                            <li>
                                                <a href="/<?= htmlspecialchars($level['slug']) ?>/<?= htmlspecialchars($section['slug']) ?>/<?= htmlspecialchars($lesson['slug']) ?>" class="lesson-link">
                                                    <?= htmlspecialchars($lesson['title_ru']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>
    
    <footer class="app-footer">
        <p>DOMLearn &copy; <?= date('Y') ?></p>
    </footer>
</body>
</html>