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

	.filament-fullcalendar .fc-toolbar-title {
		color: var(--app-text);
		font-size: 1.1rem;
		font-weight: 700;
	}

	.filament-fullcalendar .fc-button {
		background: var(--app-surface-raised);
		border-color: var(--app-border-soft);
		color: var(--app-text);
		box-shadow: none;
		text-transform: capitalize;
	}

	.filament-fullcalendar .fc-button-primary:not(:disabled).fc-button-active,
	.filament-fullcalendar .fc-button-primary:not(:disabled):active {
		background: #f7b84b;
		border-color: #f7b84b;
		color: #111827;
	}

	.filament-fullcalendar .fc-theme-standard td,
	.filament-fullcalendar .fc-theme-standard th,
	.filament-fullcalendar .fc-theme-standard .fc-scrollgrid {
		border-color: var(--app-border-soft);
	}

	.filament-fullcalendar .fc-dayGridMonth-view .fc-daygrid-day {
		height: 8.25rem;
		vertical-align: top;
		padding: 0.22rem;
		background: transparent;
	}

	.filament-fullcalendar .fc-dayGridMonth-view .fc-daygrid-day-frame {
		height: 100%;
		padding: 0.35rem;
		display: flex;
		flex-direction: column;
		border: 1px solid var(--app-border-soft);
		border-radius: 0.95rem;
		background: var(--app-surface-raised);
		box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
	}

	.filament-fullcalendar .fc-dayGridMonth-view .fc-daygrid-day-top {
		justify-content: flex-end;
	}

	.filament-fullcalendar .fc-dayGridMonth-view .fc-daygrid-day-number {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 2rem;
		height: 2rem;
		border-radius: 999px;
		color: var(--app-text);
		font-weight: 700;
	}

	.filament-fullcalendar .fc-dayGridMonth-view .fc-day-today .fc-daygrid-day-number {
		background: rgba(247, 184, 75, 0.2);
	}

	.filament-fullcalendar .fc-dayGridMonth-view .fc-daygrid-day-events {
		flex: 1 1 auto;
		margin-top: 0.35rem;
		overflow: hidden;
	}

	.filament-fullcalendar .schedule-calendar__month-event-host {
		margin: 0;
		border: 0;
		background: rgba(14, 165, 233, 0.18);
		color: var(--app-text);
		border-radius: 999px;
		overflow: hidden;
	}

	.filament-fullcalendar .schedule-calendar__month-event-host:hover {
		background: rgba(14, 165, 233, 0.24);
	}

	.filament-fullcalendar .schedule-calendar__month-event-host--mobile {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 1.75rem;
		min-width: 1.75rem;
		background: transparent;
	}

	.filament-fullcalendar .schedule-calendar__month-event {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		max-width: 100%;
		padding: 0.22rem 0.45rem;
		overflow: hidden;
		white-space: nowrap;
	}

	.filament-fullcalendar .schedule-calendar__month-time {
		flex: 0 0 auto;
		font-size: 0.7rem;
		font-weight: 700;
	}

	.filament-fullcalendar .schedule-calendar__month-label {
		min-width: 0;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 0.72rem;
		font-weight: 700;
	}

	.filament-fullcalendar .schedule-calendar__dot {
		display: inline-flex;
		width: 0.55rem;
		height: 0.55rem;
		border-radius: 999px;
		background: currentColor;
	}

	@media (max-width: 640px) {
		.filament-fullcalendar .fc-toolbar {
			gap: 0.75rem;
		}

		.filament-fullcalendar .fc-toolbar.fc-header-toolbar {
			flex-direction: column;
			align-items: stretch;
		}

		.filament-fullcalendar .fc-toolbar-chunk {
			display: flex;
			justify-content: space-between;
			gap: 0.5rem;
			flex-wrap: wrap;
		}

		.filament-fullcalendar .fc-dayGridMonth-view .fc-daygrid-day {
			height: 4.9rem;
			padding: 0.14rem;
		}

		.filament-fullcalendar .fc-dayGridMonth-view .fc-daygrid-day-frame {
			padding: 0.2rem;
			border-radius: 0.7rem;
		}
	}
</style>