# Banking Module

Implements the banking views and actions in `attachments/banking-views-and-actions.md`.

## Implemented Views
- Banking Dashboard
- Bank Transactions: index, create, show (with categorize/link/edit/delete modal actions)
- Bank Reconciliations: index, create, show (match transaction, create adjustment, complete)
- Bank Transfers: index, create, show
- Bank Accounts: index, create

## Automation
- New transactions update account balances automatically.
- Categorization and journal linking are logged as `form automation` in `change_log`.
- Reconciliation matching and completion update statuses automatically and write change logs.
- Transfers update both source and destination account balances.

## Routes
- Prefix: `/banking`
- Names: `banking.*`
