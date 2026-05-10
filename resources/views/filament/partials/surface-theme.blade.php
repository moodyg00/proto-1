<style>
	:root {
		--app-surface-panel: #ffffff;
		--app-surface-raised: #f8fafc;
		--app-surface-elevated: #ffffff;
		--app-surface-inset: #f1f5f9;
		--app-border-soft: #e2e8f0;
		--app-border-strong: #cbd5e1;
		--app-text: #0f172a;
		--app-text-muted: #475569;
		--app-text-subtle: #64748b;
		--app-shadow-panel: 0 20px 25px -10px rgba(15, 23, 42, 0.12), 0 10px 14px -10px rgba(15, 23, 42, 0.1);
		--app-shadow-raised: 0 24px 38px -20px rgba(15, 23, 42, 0.16), 0 14px 20px -18px rgba(15, 23, 42, 0.12);
	}

	html.dark {
		--app-surface-panel: #18181b;
		--app-surface-raised: #27272a;
		--app-surface-elevated: #323238;
		--app-surface-inset: #141417;
		--app-border-soft: rgba(255, 255, 255, 0.08);
		--app-border-strong: rgba(255, 255, 255, 0.12);
		--app-text: #f4f4f5;
		--app-text-muted: #d4d4d8;
		--app-text-subtle: #a1a1aa;
		--app-shadow-panel: 0 24px 38px -18px rgba(0, 0, 0, 0.65), 0 14px 20px -16px rgba(0, 0, 0, 0.5);
		--app-shadow-raised: 0 28px 42px -24px rgba(0, 0, 0, 0.72), 0 16px 24px -18px rgba(0, 0, 0, 0.56);
	}

	.app-surface-panel {
		background-color: var(--app-surface-panel);
		border-color: var(--app-border-soft);
		color: var(--app-text);
		box-shadow: var(--app-shadow-panel);
	}

	.app-surface-raised {
		background-color: var(--app-surface-raised);
		border-color: var(--app-border-soft);
		color: var(--app-text);
		box-shadow: var(--app-shadow-raised);
	}

	.app-surface-elevated {
		background-color: var(--app-surface-elevated);
		border-color: var(--app-border-soft);
		color: var(--app-text-muted);
	}

	.app-surface-inset {
		background-color: var(--app-surface-inset);
		border-color: var(--app-border-soft);
		color: var(--app-text);
	}

	.lead-board-banner,
	.lead-board-control,
	.lead-board-column {
		background-color: var(--app-surface-panel);
		border-color: var(--app-border-soft);
		color: var(--app-text);
		box-shadow: var(--app-shadow-panel);
	}

	.lead-board-switch-action {
		color: var(--app-text-subtle);
	}

	.lead-board-switch-action:hover {
		background-color: var(--app-surface-raised);
		color: var(--app-text);
	}

	.lead-board-switch-active,
	.lead-board-card {
		background-color: var(--app-surface-raised);
		border-color: var(--app-border-soft);
		color: var(--app-text);
		box-shadow: var(--app-shadow-raised);
	}

	.lead-board-pill {
		background-color: var(--app-surface-elevated);
		border-color: var(--app-border-soft);
		color: var(--app-text-muted);
	}

	.lead-board-column {
		scroll-behavior: auto;
	}

	.lead-board-card,
	.lead-item,
	.lead-board-column [data-lead-list] {
		touch-action: pan-y !important;
	}

	.lead-board-card,
	.lead-item {
		user-select: none;
		-webkit-user-select: none;
		-webkit-touch-callout: none;
		will-change: transform, box-shadow;
	}

	.lead-board-sortable-chosen {
		cursor: grabbing !important;
		opacity: 0.96 !important;
		transform: scale(1.015) !important;
		box-shadow: 0 28px 48px -20px rgba(15, 23, 42, 0.28), 0 22px 28px -24px rgba(15, 23, 42, 0.24) !important;
	}

	html.dark .lead-board-sortable-chosen {
		box-shadow: 0 30px 52px -22px rgba(0, 0, 0, 0.72), 0 22px 30px -24px rgba(0, 0, 0, 0.6) !important;
	}

	.lead-board-sortable-drag {
		z-index: 60 !important;
		cursor: grabbing !important;
		transition: none !important;
		rotate: 1deg;
	}

	.lead-board-sortable-ghost {
		opacity: 0.3 !important;
		background-color: var(--app-surface-raised) !important;
		border-style: dashed !important;
		box-shadow: none !important;
	}

	.sortable-fallback.lead-board-card,
	.lead-board-sortable-drag.sortable-fallback {
		pointer-events: none;
		opacity: 0.98 !important;
		transform: scale(1.02) !important;
		box-shadow: 0 34px 54px -24px rgba(15, 23, 42, 0.3), 0 24px 34px -26px rgba(15, 23, 42, 0.22) !important;
	}

	html.dark .sortable-fallback.lead-board-card,
	html.dark .lead-board-sortable-drag.sortable-fallback {
		box-shadow: 0 34px 58px -24px rgba(0, 0, 0, 0.78), 0 24px 34px -24px rgba(0, 0, 0, 0.62) !important;
	}
</style>