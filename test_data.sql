-- test_data.sql: Тестовые данные для проекта DOMLearn (окончательная версия)

-- Очистка таблиц (удаляем все существующие данные)
DELETE FROM lessons;
DELETE FROM sections;
DELETE FROM levels;

-- Вставка уровней
INSERT INTO levels (id, title_ru, slug) VALUES
(1, 'Начало', 'start'),
(2, 'Основы', 'basics'),
(3, 'Углубление', 'deep'),
(4, 'Продвинутое', 'advanced'),
(5, 'Гуру', 'guru');

-- Вставка разделов для уровня "Начало"
INSERT INTO sections (level_id, title_ru, slug, order_num) VALUES
(1, 'Введение в DOM', 'intro', 1),
(1, 'Базовые элементы', 'basic-elements', 2);

-- Вставка разделов для уровня "Основы"
INSERT INTO sections (level_id, title_ru, slug, order_num) VALUES
(2, 'Работа с элементами', 'working-with-elements', 1),
(2, 'События', 'events', 2);

-- Вставка уроков для раздела "Введение в DOM"
INSERT INTO lessons (section_id, title_ru, slug, content, order_num) VALUES
(
  (SELECT id FROM sections WHERE slug = 'intro'),
  'Что такое DOM?',
  'what-is-dom',
  '{
    "theory": "<h2>Что такое DOM?</h2><p>DOM (Document Object Model) - это программный интерфейс для HTML и XML документов. Он представляет структуру документа в виде дерева объектов, где каждый узел является объектом, представляющим часть документа.</p><p>DOM позволяет:</p><ul><li>Изменять структуру документа</li><li>Модифицировать содержимое элементов</li><li>Добавлять и удалять элементы</li><li>Обрабатывать события</li></ul>",
    "tests": [
      {
        "question": "Что означает аббревиатура DOM?",
        "answers": [
          "Document Object Model",
          "Data Object Management",
          "Document Order Model",
          "Digital Object Matrix"
        ],
        "correct_index": 0
      },
      {
        "question": "Какое из утверждений о DOM верно?",
        "answers": [
          "DOM представляет документ в виде дерева объектов",
          "DOM используется только для HTML документов",
          "DOM не позволяет изменять структуру документа",
          "DOM является частью языка JavaScript"
        ],
        "correct_index": 0
      }
    ],
    "tasks": [
      {
        "title": "Проверка знаний",
        "description": "Откройте консоль разработчика в браузере и выполните команду: document.documentElement. Опишите, что вы видите."
      }
    ]
  }',
  1
),
(
  (SELECT id FROM sections WHERE slug = 'intro'),
  'Дерево DOM',
  'dom-tree',
  '{
    "theory": "<h2>Дерево DOM</h2><p>Документ представлен в виде дерева узлов. Основные типы узлов:</p><ul><li><strong>Документ</strong> - корневой узел (document)</li><li><strong>Элементы</strong> - HTML теги (div, p, span и т.д.)</li><li><strong>Текстовые узлы</strong> - текстовое содержимое элементов</li><li><strong>Атрибуты</strong> - свойства элементов (class, id и т.д.)</li></ul><p>Пример структуры:</p><pre>&lt;html&gt;<br>  &lt;head&gt;<br>    &lt;title&gt;Пример&lt;/title&gt;<br>  &lt;/head&gt;<br>  &lt;body&gt;<br>    &lt;h1&gt;Заголовок&lt;/h1&gt;<br>    &lt;p&gt;Абзац текста&lt;/p&gt;<br>  &lt;/body&gt;<br>&lt;/html&gt;</pre>",
    "tests": [
      {
        "question": "Какой узел является корневым в DOM дереве?",
        "answers": [
          "document",
          "html",
          "body",
          "head"
        ],
        "correct_index": 0
      }
    ],
    "tasks": [
      {
        "title": "Анализ структуры",
        "description": "Создайте простую HTML страницу с несколькими элементами и изучите её структуру в инструментах разработчика браузера."
      }
    ]
  }',
  2
);

-- Вставка уроков для раздела "Базовые элементы"
INSERT INTO lessons (section_id, title_ru, slug, content, order_num) VALUES
(
  (SELECT id FROM sections WHERE slug = 'basic-elements'),
  'Поиск элементов',
  'finding-elements',
  '{
    "theory": "<h2>Поиск элементов в DOM</h2><p>Для работы с элементами страницы необходимо сначала найти их в DOM. Основные методы:</p><ul><li><code>document.getElementById(id)</code> - поиск по ID</li><li><code>document.querySelector(selector)</code> - поиск по CSS селектору (первый элемент)</li><li><code>document.querySelectorAll(selector)</code> - поиск всех элементов по селектору</li><li><code>document.getElementsByClassName(className)</code> - поиск по классу</li><li><code>document.getElementsByTagName(tagName)</code> - поиск по тегу</li></ul>",
    "tests": [
      {
        "question": "Какой метод вернет один элемент?",
        "answers": [
          "document.querySelector",
          "document.querySelectorAll",
          "document.getElementsByClassName",
          "document.getElementsByTagName"
        ],
        "correct_index": 0
      }
    ],
    "tasks": [
      {
        "title": "Практика поиска",
        "description": "На странице с несколькими элементами найдите все элементы с классом \"test\" используя разные методы."
      }
    ]
  }',
  1
);

-- Вставка уроков для раздела "Работа с элементами"
INSERT INTO lessons (section_id, title_ru, slug, content, order_num) VALUES
(
  (SELECT id FROM sections WHERE slug = 'working-with-elements'),
  'Изменение содержимого',
  'modifying-content',
  '{
    "theory": "<h2>Изменение содержимого элементов</h2><p>После получения ссылки на элемент можно изменять его содержимое и свойства:</p><ul><li><code>element.innerHTML</code> - HTML содержимое элемента</li><li><code>element.textContent</code> - текстовое содержимое</li><li><code>element.setAttribute(name, value)</code> - установка атрибута</li><li><code>element.style.property</code> - изменение CSS стилей</li><li><code>element.classList</code> - управление классами (add, remove, toggle)</li></ul>",
    "tests": [
      {
        "question": "Какое свойство следует использовать для безопасной вставки текста?",
        "answers": [
          "textContent",
          "innerHTML",
          "innerText",
          "outerHTML"
        ],
        "correct_index": 0
      },
      {
        "question": "Как добавить класс \"active\" к элементу?",
        "answers": [
          "element.classList.add(\"active\")",
          "element.addClass(\"active\")",
          "element.className += \" active\"",
          "element.setClass(\"active\")"
        ],
        "correct_index": 0
      }
    ],
    "tasks": [
      {
        "title": "Динамическое изменение",
        "description": "Создайте кнопку, которая при клике изменяет текст и цвет другого элемента на странице."
      }
    ]
  }',
  1
);

-- Вставка уроков для раздела "События"
INSERT INTO lessons (section_id, title_ru, slug, content, order_num) VALUES
(
  (SELECT id FROM sections WHERE slug = 'events'),
  'Обработка событий',
  'event-handling',
  '{
    "theory": "<h2>Обработка событий</h2><p>События - это действия, происходящие на веб-странице (клики, наведение, ввод и т.д.). Основные способы обработки:</p><ul><li>HTML атрибут: <code>&lt;button onclick=\"handler()\"&gt;</code></li><li>Свойство элемента: <code>element.onclick = handler</code></li><li>Метод addEventListener: <code>element.addEventListener(\"click\", handler)</code></li></ul><p>Преимущества addEventListener:</p><ul><li>Можно добавить несколько обработчиков</li><li>Более тонкий контроль (всплытие, захват)</li><li>Возможность удаления обработчика</li></ul>",
    "tests": [
      {
        "question": "Какой метод позволяет добавить несколько обработчиков на одно событие?",
        "answers": [
          "addEventListener",
          "onclick",
          "Оба варианта",
          "Ни один из вариантов"
        ],
        "correct_index": 0
      }
    ],
    "tasks": [
      {
        "title": "Создание интерактивного элемента",
        "description": "Создайте кнопку, которая меняет свой цвет при наведении и возвращает исходный при уходе курсора."
      }
    ]
  }',
  1
);