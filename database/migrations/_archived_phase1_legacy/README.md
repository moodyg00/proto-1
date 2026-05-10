# Archived Legacy Migrations (Phase 1)

These migration files were moved out of the active migration path to avoid conflicts with:
- `2026_05_03_000000_bootstrap_phase1_schema.php`
- `schema.sql` (PostgreSQL-first authoritative schema)

Reason:
- The archived files create overlapping domain tables (users/domain entities) that duplicate the new Phase 1 schema.

Still active in `database/migrations`:
- framework/support migrations (cache/jobs/personal_access_tokens/permissions/media)
- `2026_05_03_000000_bootstrap_phase1_schema.php`
