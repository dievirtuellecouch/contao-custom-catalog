# Bundle review — 2026-09-17

Reviewed all tracked PHP classes, DCA definitions, services, migrations, templates, language files, JavaScript and Composer metadata at tag 1.0.3, against Contao 5.3.44 / PHP 8.4 / MariaDB 10.11. Versions 1.0.4–1.0.5 contain the fixes listed in CHANGELOG.md.

## Confirmed primary defect

The branch backend used `überschrift` as a database column/DCA field. Contao's Database Statement validates column identifiers as ASCII. Changing the optional headline triggers `Invalid column name "überschrift"` and HTTP 500; unchanged empty values may not trigger a write. The supplied logs contain repeated failures on 10/11 June and 2/16 September 2026. Reproduced through the backend with a new branch before fixing it.

The fix renames the column in place, updates the DCA and templates, and retains legacy field access through EntryWrapper. Copying rows also needs the real column renamed, so a virtual form-field workaround alone would not solve the problem. The migration aborts without changing data when both old and new columns exist.

## Other confirmed defects fixed

- Empty custom SQL result sets incorrectly fell back to every published branch.
- Coordinate-only list searches were ignored; explicit coordinates could be overwritten by an approximate city match.
- Empty, malformed and out-of-range coordinates were cast to valid-looking numbers. Serialized coordinates were not handled by one map template.
- Search requests appended duplicate URL parameters and retained old coordinates after geocoding failure. Selected radius and reader API-key propagation were inconsistent.
- Regex-based detail replacement truncated nested main divs or discarded the document head in body fallback. JSON/error responses are now excluded.
- A frontend-template listener referenced a class absent from Contao 5.3; redundant response rewriting was removed.
- Global DCA changes hid AJAX settings and overwrote jumpTo configuration for unrelated modules.
- Legacy aliases and product configuration navigation relied on the removed TL_MODE constant; the configuration link missed its query delimiter and targeted an obsolete backend DOM element. Version 1.0.5 replaces the script with a native global operation.
- Two service definitions referenced nonexistent insert-tag classes.
- Migration detection omitted the products configuration table and existing-table additions. Module field migration omitted API-key and SQL-filter columns. Silent column failures are now reported, and unrelated potentially truncating type changes were removed.
- Plain address output and JSON script content lacked appropriate escaping. Direct unserialization now disables object instantiation.

## Remaining design limitations / separate work

- The generic product/person list palettes have no corresponding registered frontend implementation. Existing modules named “Produkte Liste” and “Person Teaser” are configured as branch lists. The active branch reader renders its products/contact person directly. Implementing independent product/person list modules needs a separate functional specification.
- The products configuration table exposes settings such as `useTitleAsName`, `list_fields`, `list_order`, and `showMenu`, but the bundle does not apply these settings. The link now opens the configuration; implementing those behaviors is separate feature work.
- Templates deliberately contain EHA-specific gallery UUIDs, image size IDs, an article alias and an external gallery-template dependency. They are not portable defaults for unrelated websites. The supplied EHA installation includes the required template/assets.
- Offline location search estimates coordinates from matching branches/ZIP prefixes; it is not a complete geocoding database. External Maps loading belongs to the site's consent integration; the bundle does not automatically inject Google scripts.
- Detail URLs still follow this installation's slash-based routing. Other suffix/domain/routing setups require integration tests.
- SQL filters are trusted backend configuration, not public query input. No public request value is interpolated directly into SQL by these modules.
- No claim is made that a static review and targeted tests prove absence of all defects.

## Validation

- PHP/template syntax checks and Composer metadata validation.
- `tests/regression.php`: coordinate edge cases, legacy headline access, nested HTML replacement, response types, shared DCA preservation.
- `tests/migrations.php`: isolated database tests for Unicode/empty value retention, idempotence, dual-column refusal, fresh installation, missing-table/column detection and module fields.
- Actual Contao module compile checks: empty custom filter, no matching geography, coordinate-only radius.
- Browser tests in the development copy: original error reproduction; save non-empty headline; clear headline; new branch and copy; published detail; unpublished/unknown 404; AJAX searches with and without matches.

Other historical logs relate to connection limits, missing tables during an earlier import, SMTP sender rejection, an invalid form email and a missing image. They do not establish additional defects in this bundle. PHP 8.4 deprecation notices in unrelated dependencies remain outside this release.
