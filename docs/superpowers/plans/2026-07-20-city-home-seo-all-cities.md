# City homepage SEO for all cities Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the existing homepage city SEO section editable for every city and publicly available for every published city.

**Architecture:** Reuse `ContentMigration` as the single idempotent seed path. It will use an already-complete city as the shared-media source, generate Ukrainian city-specific copy, and write only empty `city_home_seo_*` fields; the current REST route and frontend continue unchanged.

**Tech Stack:** WordPress, PHP 8.3, ACF Pro Local JSON, DDEV.

## Global Constraints

- Do not change the existing ACF field names, REST response, markup, JavaScript, or editor-entered values.
- Copy and admin labels must remain Ukrainian.
- Rerunning the seed must not overwrite content or create media.

---

### Task 1: Seed complete homepage SEO content for all cities

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/src/ContentMigration.php`
- Modify: `tests/homepage-city-seo.php`
- Modify: `docs/plan.md`

**Interfaces:**
- Consumes: existing `ContentMigration::fill()`, a complete published city's `city_home_seo_*` media fields, and `CityApi::homepageSeo()`.
- Produces: `ContentMigration::seedHomepageCitySeo(bool $dry_run): array`, safe to run repeatedly.

- [x] **Step 1: Write the failing migration assertions**

Extend `tests/homepage-city-seo.php` with a published city that has no city-home-SEO values and another with an editor-defined title. Require `ContentMigration::seedHomepageCitySeo()` to make the empty city return a complete REST payload containing its city name, while retaining the editor-defined title after a second run.

- [x] **Step 2: Verify red**

Run: `ddev exec php tests/homepage-city-seo.php`

Expected: failure because `seedHomepageCitySeo()` does not exist.

- [x] **Step 3: Implement the smallest idempotent seed**

Add `seedHomepageCitySeo()` to `ContentMigration`. Select published cities ordered by ID; find one complete section as a media source; for every city, call `fill()` with generated title, description, CTA, caption, source image IDs, and source video URL. Return the existing migration report without creating attachments.

- [x] **Step 4: Verify green and runtime behavior**

Run: `ddev exec php tests/homepage-city-seo.php && ddev exec php -l wordpress/wp-content/plugins/logika-core/src/ContentMigration.php`

Expected: the test prints `Homepage city SEO API and markup handle complete city content safely.` and PHP reports no syntax errors.

- [x] **Step 5: Seed the local database and verify all published cities**

Run: `ddev wp eval 'print_r( Logika\\Core\\ContentMigration::seedHomepageCitySeo() );'` followed by `ddev wp eval '$cities = get_posts(["post_type"=>"city","post_status"=>"publish","posts_per_page"=>-1,"fields"=>"ids"]); foreach ($cities as $id) { if (!rest_do_request(new WP_REST_Request("GET", "/logika/v1/cities/$id/homepage-seo"))->get_data()) { fwrite(STDERR, "$id\\n"); exit(1); } } echo count($cities), " published cities complete\\n";'`

Expected: no IDs are printed and every published city has a complete public section.

- [x] **Step 6: Record completion and commit only task files**

Mark the task complete in `docs/plan.md`, update `docs/changelog/2026-07.md`, then commit only the task files with `git commit -m "feat: seed homepage SEO for cities"`.
