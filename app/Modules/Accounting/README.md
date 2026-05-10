# Accounting Module

This module implements the accounting views/actions from `attachments/accounting-views-and-actions.md`.

## Implemented Views
- Dashboard
- Invoices: index, create, show, edit, delete, record payment (modal)
- Bills: index, create, show, edit, delete, record payment (modal)
- Payments: index
- Journal Entries: index

## Automation
- Invoice payment recording updates `amount_paid`, `amount_due`, and status transitions (`draft/sent -> partial -> paid`).
- Bill payment recording updates `amount_paid`, `amount_due`, and status transitions (`received/approved -> partial -> paid`).
- Both payment actions write to `change_logs` with action metadata.

## Routes
- Prefix: `/accounting`
- Names: `accounting.*`
