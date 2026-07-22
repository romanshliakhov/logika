# Mobile Top-Bar Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show email and social icons above the city and phone on small screens, with email aligned to city text and contact icons visible, without changing tablet or desktop headers.

**Architecture:** Reuse the shared header flex container. At `max-width: 767px`, wrap it and place the existing email and social blocks in the first row; city and phone naturally occupy the second row. No markup or JavaScript changes are needed.

**Tech Stack:** SCSS, compiled CSS, WordPress theme CSS, Playwright CLI.

## Global Constraints

- Public UI remains Ukrainian.
- Change only the shared mobile header styles.
- Preserve existing dirty worktree changes.

---

### Task 1: Two-row mobile top bar

**Files:**
- Modify: `source/scss/blocks/_header.scss:16-105`
- Modify: `build/css/style.css:2591-2584`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/adaptive.css:171-185`

**Interfaces:**
- Consumes: existing `.header__top-box`, `.header__socials`, `.header__top-city`, and `.header__contact--tel` elements.
- Produces: a wrapped mobile top bar with email and social icons first and city/phone second.

- [x] Add the mobile flex layout.

```scss
@include small-tablet {
    flex-wrap: wrap;
    gap: 4px 12px;
}
```

```scss
&--email { @include small-tablet { display: flex; order: -1; flex: 1; justify-content: flex-start; } }
&__socials { @include small-tablet { order: -1; flex-shrink: 0; } }
```

- [x] Mirror the compiled rule into the live theme `adaptive.css`.

- [x] Verify with Playwright at 320 px that email and socials render above city/phone, the phone is one line, and `document.documentElement.scrollWidth === innerWidth`; verify the 768 px layout remains unchanged.

- [x] Run `git diff --check`. Do not commit the CSS because the target files already contain user-owned uncommitted changes.

### Task 2: Compact tablet top bar

- [x] For 768–1024 px, hide contact/social headings, reduce gaps, and keep phone/email links on one line.

### Task 3: Protect desktop city selector

- [x] For 1025–1440 px, prevent the logo container and city selector from shrinking into one another.
