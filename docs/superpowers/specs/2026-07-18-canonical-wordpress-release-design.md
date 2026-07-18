# Канонічний WordPress release

## Мета

Staging отримує рівно один перевірений WordPress runtime з
`.worktrees/wordpress`. Зміни з інших checkout не можуть непомітно потрапити
або не потрапити до release.

## Межа release

Release пакує весь власний runtime WordPress: `logika-theme`, `logika-core`,
`logika-leads` і версіоновані файли `wp-content/mu-plugins`. Він не пакує
WordPress core, сторонні плагіни, `uploads`, `wp-config.php`, кеші чи
тимчасові файли.

Побудовані CSS та зображення накладаються на тему. JavaScript теми береться
лише з її runtime-каталогу, тому static build не може замінити обробники
модалок.

## Потік

1. Перед release перевірка називає канонічний worktree і показує зміни в
   інших checkout, які треба перенести окремим контрольованим кроком.
2. Артефакт створюється лише з канонічного worktree та містить manifest усіх
   runtime-файлів і їх SHA-256.
3. Deploy атомарно перемикає `current`, після чого перевірка порівнює manifest
   з активним runtime на staging.
4. Якщо правка є лише у static `source/`, release зупиняється з повідомленням:
   її треба перенести до WordPress-теми або свідомо позначити як нерелізну.

## Безпека та перевірка

- Перенесення між checkout не видаляє файли та не виконується автоматично при
  deploy; конфлікти зупиняють операцію до ручного рішення.
- Тест release перевіряє повний склад manifest, збереження JS теми та
  відсутність mutable/server-only файлів в архіві.
- Staging smoke і браузерна перевірка залишаються обов'язковими після
  перемикання release.
