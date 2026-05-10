# CRM Module (Phase 2)

Implemented from crm-views-and-actions.md (initial build after Operations):

- CRM Dashboard with KPI cards, leads/opportunities/tickets sections, and Quick Links.
- Leads Index with Table/Card toggle and key filters.
- Lead Show with full details, notes/history area, and related estimates/opportunities.
- Lead Create and Edit forms.
- Service/repository + request validation pipeline for leads.
- Change logging for lead create/update/delete operations.

## TODOs

- Implement Contact, Opportunity, Ticket, and Estimate full CRUD pages.
- Implement modal actions for log call/email, convert to contact, and create estimate from lead.
- Implement first-reply ticket status automation and response-time tracking.
- Add full policy enforcement by auth roles once app authentication flow is finalized.
