<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($level['title_ru']) ?> | DOMLearn</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="app-header">
        <h1>DOMLearn: <?= htmlspecialchars($level['title_ru']) ?></h1>
        <a href="/" class="back-link">← На главную</a>
    </header>

    <main class="content-container">
        <div class="breadcrumbs">
            <a href="/">Главная</a> / <?= htmlspecialchars($level['title_ru']) ?>
        </div>

        <section class="sections-list">
            <?php if (empty($sections)): ?>
                <p>Разделы пока не добавлены</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($sections as $section): ?>
                        <li>
                            <a href="/<?= $levelSlug ?>/<?= htmlspecialchars($section['slug']) ?>">
                                <?= htmlspecialchars($section['title_ru']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
    
    <footer class="app-footer">
        <p>DOMLearn &copy; <?= date('Y') ?></p>
    </footer>
</body>
</html>