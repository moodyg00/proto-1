# Content Module

Implements views and actions from `attachments/content-views-and-actions.md`.

## Implemented Views
- Content Dashboard
- Blog Posts: index, create, show (edit, publish/schedule, unpublish, duplicate, delete)
- Pages: index, create, show (edit, publish/unpublish, duplicate, delete)
- Physical Designs: index, create, show (new version, link product, approve, archive, edit)
- Image Files index
- Assets index

## Automation
- Publish/schedule blog posts updates status and publication date automatically.
- Unpublishing blog posts sets status to archived.
- Page publish/unpublish updates both `status` and `is_published`.
- Creating design versions marks latest version automatically.
- Approving designs updates status across design and versions.
- All automated actions write `form automation` entries to `change_log`.

## Routes
- Prefix: `/content`
- Names: `content.*`
