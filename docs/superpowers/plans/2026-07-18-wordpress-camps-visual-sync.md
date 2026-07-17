# WordPress Camps Visual Sync Implementation Plan

> **For agentic workers:** Execute this plan task-by-task in the current `wordpress` worktree. Preserve unrelated dirty files.

**Goal:** Make the WordPress `/camps/` page render the camp sections and styles from `f5f61ef` while preserving existing WordPress data and form behavior.

**Architecture:** Keep the WordPress template as the runtime source. Port the approved static markup only where it changes presentation, retain PHP-driven archive URLs and content arrays, add camp SVG assets under the theme asset root, and load the existing section CSS through the current enqueue mechanism.

**Tech Stack:** WordPress theme PHP, existing ACF/CPT data flow, CSS assets, DDEV, PHP lint, browser smoke check.

## Global Constraints

- Do not change the database, ACF field definitions, WordPress core, or plugins.
- Do not overwrite unrelated local changes or generated Graphify/temp files.
- Keep all user-visible copy Ukrainian.
- Keep output escaped and preserve existing dynamic archive links.
- Reuse existing theme enqueue/versioning and existing Swiper dependency.

### Task 1: Capture baseline and isolate the change

**Files:**
- Create: `safety/camps-wp-before-visual-sync-20260718` branch only
- Test: current DDEV `/camps/` URL and `php -l` baseline

- [x] Create a safety branch from the current `wordpress` HEAD.
- [x] Record the current `/camps/` response status and theme file status.
- [x] Leave existing Graphify and temporary files unstaged.

### Task 2: Port camp markup and assets into the theme

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/template-parts/pages/camps.php`
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/camps.php`
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/camps/camps-bg.svg`
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/camps/camps-pattern.svg`
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/camps/camps1.svg` through `camps6.svg`

- [x] Keep `$highlights` and `$formats` as the data source.
- [x] Update only markup/classes needed for the new highlights slider and camp formats.
- [x] Point all new asset URLs to the theme asset root with `esc_url()`.
- [x] Preserve WordPress-generated camp archive URLs and existing form/ACF hooks.
- [x] Keep the source-page markup aligned with the runtime template.

### Task 3: Add the approved camp CSS

**Files:**
- Create or replace: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/camp-booking.css`
- Create or replace: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/camp-formats.css`
- Create or replace: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/camp-highlights.css`
- Create or replace: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/camp-page-hero.css`

- [x] Port the generated styles from the approved static build.
- [x] Adjust only relative image paths from static `build/img` to theme `assets/img`.
- [x] Do not add a new stylesheet loader; use existing per-section enqueue logic and `filemtime` cache busting.

### Task 4: Verify the WordPress runtime

**Files:**
- Test: changed PHP files, DDEV `/camps/`, CSS/SVG asset URLs

- [x] Run `php -l` for changed PHP templates.
- [x] Run the existing frontend build/HTML validation without staging generated output.
- [x] Open `http://logika.ddev.site/camps/` and verify the new sections and assets render.
- [x] Check browser console/network for missing camp CSS or SVG files.
- [x] Review `git diff --check` and confirm only intended theme files plus the plan are changed.
- [x] Create one focused commit for the WordPress visual sync.
