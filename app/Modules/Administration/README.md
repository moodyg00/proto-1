# Administration Module

## Purpose
Core system configuration and audit module. Manages users, system settings, offered services, products, inventory, and provides a full change-log audit trail.

## Key Views
| Route | Description |
|---|---|
| `/administration/dashboard` | KPI overview, recent activity, quick links |
| `/administration/users` | Users index (table/card toggle) |
| `/administration/users/{id}` | User profile with activity, tickets, tasks |
| `/administration/users/create` | Create user form |
| `/administration/settings` | Settings grouped by module |
| `/administration/services` | Services index (table/card toggle) |
| `/administration/services/{id}` | Service detail with edit modal |
| `/administration/products` | Products index (table/card toggle) |
| `/administration/products/{id}` | Product detail + inventory + adjust stock |
| `/administration/inventory` | Inventory stock levels with low-stock alerts |
| `/administration/change-log` | Full audit trail (filterable) |
| `/administration/change-log/{id}` | Single change-log entry detail |

## Automation Rules
- Deactivating a user automatically unassigns their open tasks and tickets, and logs the action.
- Adjusting stock below reorder level automatically creates a "Reorder stock" Task and logs a `low_stock` alert.
- All create/update/delete operations are logged to the `change_log` table.

## Stack
- **Repository**: `app/Repositories/Administration/AdministrationRepository.php`
- **Service**: `app/Services/Administration/AdministrationService.php`
- **Controllers**: `app/Http/Controllers/Administration/`
- **Requests**: `app/Http/Requests/Administration/`
- **Pages**: `resources/js/Pages/Administration/`
