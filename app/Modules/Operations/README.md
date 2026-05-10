# Operations Module (Phase 2)

Implemented from operations-views-and-actions.md:

- Dashboard view with KPI cards, Work Orders section, Operations Tickets section, and Quick Links.
- Work Orders Index with Card/Table toggle and quick filters (status, due date, assigned contractor).
- Work Order Show with modal-heavy actions:
  - Assign Contractor modal
  - Add Material modal
  - Create Booking modal
  - Upload Photo modal
  - Quick status change action
- Work Order Create and Edit forms.
- Automation rules implemented in service layer:
  - Booking creation sets status to scheduled.
  - Status completed creates Quality Review task.
  - Contractor assignment keeps new work orders in scheduled when applicable.
- All create/update/delete/automation actions are logged to change_log.

## TODOs

- Add dedicated UI for invoice creation from Work Order Show when invoice is missing.
- Add photo gallery/carousel modal with delete action.
- Add quality review queue widget and deep links in dashboard.
- Add tests for status automation and modal action endpoints.
