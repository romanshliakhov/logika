# Article Tag Filter Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Відкривати архів статей, відфільтрований за тегом статті.

**Architecture:** Посилання передає slug у `tag`; наявний `MediaApi` додає його до поточного `post_tag` запиту; скрипт архіву передає параметр до REST API.

**Tech Stack:** WordPress, PHP, REST API, vanilla JavaScript.

## Global Constraints

- Лише український публічний текст.
- Не додавати маршрут, залежність або таксономію.

---

### Task 1: Фільтр і посилання тегу

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/src/ArticlePage.php`
- Modify: `wordpress/wp-content/themes/logika-theme/functions.php`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/js/media-center.js`
- Modify: `wordpress/wp-content/plugins/logika-core/src/MediaApi.php`
- Test: `tests/media-api.php`, `tests/media-center-articles.php`

- [x] Додати спершу перевірки API та посилання тегу.
- [x] Передати `tag` від архіву до REST API.
- [x] Додати `post_tag` умову до міських і загальних вибірок API.
- [x] Перетворити теги статті на посилання до `/media-center/articles/?tag={slug}`.
- [x] Запустити регресійні PHP-перевірки та синтаксичну перевірку файлів.
