# Mobile top-bar layout

## Scope

Apply only at `max-width: 767px` in the shared header.

- Move social icons to their own first row, aligned right.
- Keep the current city and phone in the second row, with the phone on one line.
- Preserve the existing tablet and desktop layout, header markup, city logic and links.

## Implementation

Use the existing flex container: enable wrapping, make `.header__socials` full-width with first visual order, and retain the city/phone elements below it. No JavaScript or markup changes.

## Verification

- At 375 px social icons are above the city and phone.
- The phone number stays on one line with no horizontal overflow.
- At 768 px the current single-row tablet layout is unchanged.
