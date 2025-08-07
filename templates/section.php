<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($section['title_ru']) ?> | DOMLearn</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="app-header">
        <h1><?= htmlspecialchars($level['title_ru']) ?>: <?= htmlspecialchars($section['title_ru']) ?></h1>
        <a href="/<?= $levelSlug ?>" class="back-link">← Назад к уровню</a>
    </header>

    <main class="content-container">
        <div class="breadcrumbs">
            <a href="/">Главная</a> / 
            <a href="/<?= $levelSlug ?>"><?= htmlspecialchars($level['title_ru']) ?></a> / 
            <?= htmlspecialchars($section['title_ru']) ?>
        </div>

        <section class="lessons-list">
            <?php if (empty($lessons)): ?>
                <p>Уроки пока не добавлены</p>
            <?php else: ?>
                <ol>
                    <?php foreach ($lessons as $lesson): ?>
                        <li>
                            <a href="/<?= $levelSlug ?>/<?= $sectionSlug ?>/<?= htmlspecialchars($lesson['slug']) ?>">
                                <?= htmlspecialchars($lesson['title_ru']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>
    </main>
    
    <footer class="app-footer">
        <p>DOMLearn &copy; <?= date('Y') ?></p>
    </footer>
</body>
</html>