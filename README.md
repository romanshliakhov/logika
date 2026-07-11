# Project

The project is a multi-page landing site built for an educational school in Ukraine for children and teenagers for programming and English language training.  
The project does not include personal account functionality; it only supports enrollment application submission.  
The site serves as a sales page so users ultimately submit an enrollment request.  
It is important to enable a high level of customization so all texts and images can be changed dynamically and blog posts can be written. This is achieved with ACF Pro.  
One of the main technical features is selecting a city on an interactive map or in the navbar, after which regional news and regional promotions for a specific location are displayed.

## Leads

Lead submissions use `Browser → WordPress → CRM`. Run the local WordPress stack with DDEV; production must trigger WordPress cron so failed CRM deliveries are retried. CRM provider and secrets are supplied only through server environment variables (`LOGIKA_CRM_PROVIDER` and provider webhook/token values), never in frontend JavaScript.

## Локальний запуск

1. Додайте `ACF_PRO_LICENSE_KEY` до кореневого `.env`.
2. У корені проєкту виконайте `ln -s ../.env .ddev/.env` та `ddev start`.
3. Відкрийте [http://logika.ddev.site](http://logika.ddev.site).

Адмінка доступна за `/wp-admin`. Для зміни локального пароля виконайте:

```bash
ddev exec wp --path=/var/www/html/wordpress user update logika_editor --user_pass='новий-надійний-пароль'
```

Перевірка контентної моделі:

```bash
ddev exec php /var/www/html/tests/wordpress-smoke.php
```

## Редагування контенту

- Головна сторінка: `Сторінки → Головна`. Блок ACF `Головна сторінка` містить hero-тексти, hero-зображення, переваги та секцію англійської.
- Інші статичні сторінки: `Сторінки → Про Logika`, `FAQ`, `IT курси`, `Курси англійської`, `Медіа-центр`.
- Повторюваний контент редагується окремо: `Міста`, `Філії`, `Курси`, `Табори`, `Відгуки`, `FAQ`.
- Глобальні телефони, CTA, fallback-тексти та SEO-шаблони: `Налаштування сайту`.

Оновити прев'ю всіх поточних зображень головної у ACF-галереї:

```bash
ddev exec wp --path=/var/www/html/wordpress eval-file /var/www/html/scripts/seed-home-gallery.php
```

Заповнити клікабельні зображення біля секцій головної:

```bash
ddev exec wp --path=/var/www/html/wordpress eval-file /var/www/html/scripts/seed-home-section-images.php
```

Відновити тексти головної сторінки в ACF з поточної вихідної верстки:

```bash
ddev exec wp --path=/var/www/html/wordpress eval-file /var/www/html/scripts/seed-home-texts.php
```

Додати або оновити публічні міста для селектора:

```bash
ddev exec wp --path=/var/www/html/wordpress eval-file /var/www/html/scripts/seed-cities.php
```
