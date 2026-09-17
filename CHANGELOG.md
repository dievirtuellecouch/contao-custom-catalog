# Changelog

## 1.0.4 — 2026-09-17

- Fix saving and copying branches with a non-empty headline in Contao 5.3: rename the legacy `überschrift` column to `headline`, retaining all values. Existing EntryWrapper template calls using `überschrift` remain supported.
- Keep empty custom SQL filters empty; honor coordinate-only radius searches and explicit geocoded centers.
- Validate coordinates, reject empty/out-of-range values, safely deserialize arrays, and clamp distance rounding.
- Preserve nested main containers, document head and footer when rendering branch details; do not replace JSON or error responses. Remove the obsolete listener for a nonexistent Contao event.
- Preserve shared Contao module field definitions and AJAX controls of unrelated modules.
- Repair legacy module aliases and the product configuration link on Contao 5.
- Detect missing configuration tables and migration columns; include maps/filter module fields and report column-migration failures.
- Remove service definitions for nonexistent insert-tag classes.
- Preserve selected search radius, replace old query parameters, clear stale coordinates, and pass Maps configuration to the reader. External Maps scripts remain the responsibility of the site's consent loader; the search uses the offline fallback until Maps is loaded.
- Escape plain address output and JSON script data; handle serialized coordinates in both map templates.

### Upgrade

Back up the database and install this release. Run Contao migrations **before** database schema cleanup, so `RenameBranchHeadlineMigration` can rename the original column without losing its contents. The migration is idempotent and refuses to change data if both column names already exist. It runs with priority 100, ahead of the existing catalog migrations. Clear the production cache afterwards.

Direct custom SQL, model access or import mappings outside this bundle that reference `überschrift` must be changed to `headline`. Calls through `EntryWrapper::field('überschrift')` or its magic property remain compatible. Test custom templates in a development environment before a production rollout.
