# Phase 1 Laravel Migration Sequence (FK-Safe)

This sequence is based on schema.sql and is ordered to avoid foreign key dependency failures.

## Important rollout note

The project already contains older migration files that overlap with this new model (users, tasks, accounting, banking, crm, ads, settings, etc.).

Use one of these approaches before generating/running new migrations:

1. Greenfield reset: run fresh database reset and run only the new Phase 1 migration set.
2. Parallel track: move old conflicting domain migrations into an archive folder and keep framework migrations only.
3. Controlled merge: manually reconcile old tables to the Phase 1 table list, then run only missing migrations.

Keep framework migrations:
- create_users_table (if you decide not to recreate users)
- cache/jobs/personal_access_tokens
- permission/media only if still required as-is

## Recommended generation order

### Group 1: Core foundations
1. users
2. password_resets
3. change_log
4. settings

### Group 2: CRM base
5. organizations
6. contacts
7. leads
8. opportunities
9. tickets
10. estimates

### Group 3: Operations base
11. services
12. products
13. inventory
14. work_orders
15. bookings
16. work_order_status_history
17. work_order_photos
18. work_order_documents
19. work_order_time_logs
20. contractor_performance
21. safety_incidents
22. reviews
23. quality_reviews
24. material_returns
25. contractor_availability
26. booking_requests
27. sops
28. service_sops
29. work_order_assignments
30. customer_signoffs

### Group 4: Accounting base
31. chart_of_accounts
32. journal_entries
33. journal_entry_lines
34. invoices
35. invoice_items
36. recurring_invoices
37. bills
38. payments
39. expenses
40. payroll
41. tax_forms
42. tax_settings
43. tax_filings
44. tax_payments
45. credits
46. material_purchases

### Group 5: Banking
47. bank_accounts
48. bank_cards
49. bank_transactions
50. bank_reconciliations
51. bank_transfers
52. bank_imports
53. bank_rules

### Group 6: Content
54. blog_categories
55. blog_tags
56. blog_posts
57. blog_post_tags
58. pages
59. page_section_types
60. image_files
61. social_media_accounts
62. social_media_content
63. assets
64. physical_designs
65. physical_design_versions
66. product_designs

### Group 7: Marketing and Ads
67. ad_campaigns
68. campaign_budgets
69. funnels
70. landing_pages
71. ads
72. ad_variants
73. campaign_performance
74. ad_creative_assets
75. ad_audiences
76. ad_placements
77. marketing_attribution

### Group 8: Integrations
78. integrations
79. api_credentials
80. webhooks
81. integration_logs
82. snippets

### Group 9: AI Tools
83. tasks
84. ai_agent_profiles
85. ai_task_runs
86. ai_alerts

### Group 10: Administration support
87. system_health_events
88. low_stock_alerts

### Group 11: Deferred cross-module FK migrations
89. add fk work_orders.invoice_id -> invoices.id
90. add fk work_order_materials.invoice_item_id -> invoice_items.id
91. add fk tickets.related_invoice_id -> invoices.id
92. add fk tickets.related_work_order_id -> work_orders.id
93. add fk material_purchases.journal_entry_id -> journal_entries.id
94. add fk physical_designs.latest_version_id -> physical_design_versions.id
95. add fk landing_pages.ad_id -> ads.id

## Why deferred FKs are split out

These constraints are cyclic or cross-group and can break normal create order if applied too early.

## Suggested implementation pattern per migration

- Up: create table, constraints, indexes, created_by/updated_by references
- Down: drop table
- Use uuid columns and default generated ids
- Use timestamptz equivalents through timestampTz columns in Laravel
- Use check constraints using DB::statement where Laravel fluent methods are limited

## Verification checklist

1. Run migration status and confirm expected order.
2. Run migrate against empty PostgreSQL database.
3. Confirm no missing table or FK errors.
4. Confirm index creation for status, assigned_to, dates, contact/customer references.
5. Confirm all tables include id, created_at, updated_at, created_by, updated_by where appropriate.

## Optional fast path

If you want to bootstrap quickly, create one temporary migration that executes schema.sql with DB::unprepared for a fresh database, then gradually replace it with granular migrations from this sequence.
